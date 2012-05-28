<?php
require_once 'WebDAV/Stream/Exception.php';
require_once "WebDAV/Client.php";

/**
 * stat mode flags: http://www.php.net/manual/en/function.stat.php#34919
 */

class WebDAV_Stream_Wrapper
{
    public static $debug = true;
    
    public $context;

    public static $_transport = 'Curl';

    /**
     * @var HTTP_URL
     */
    protected $_url;

    protected $_stat        = array();

    protected $_position    = 0;

    protected $_dirEntries  = array();

    protected $_dirPosition = 0;

    protected $_handle;

    protected $_handleChanged = false;

    /* stream functions */

    public function stream_open($path, $mode)
    {
        if (!($this->_url = $this->_parseStreamUrl($path))) {
            return false;
        }

        $this->_stat = $this->url_stat($path);

        if (!$this->_stat['mode']) { // file does not exists

            if (!preg_match('|[aw\+]|', $mode)) { // flag is not assumes the file can be created
                return false;
            }

            if (strrpos($_ = $this->_url->getPart(HTTP_URL::URL_PATH), '/') == strlen($_) - 1) {
                throw new WebDAV_Stream_Exception("Unsupported resource type (create the directory?)");
            }

            $this->_stat['mode']    = 0100000; //S_IFREG
            $this->_handle          = fopen('php://temp', 'r+');

        } else {
            if (($this->_stat['mode'] & 0170000) == 010000) { // is file
                $response           = $this->_getClient()->get($this->_url);
                $this->_handle      = fopen('php://temp', 'r+');

                stream_copy_to_stream($response->getBody(), $this->_handle);
            } else {
                throw new WebDAV_Stream_Exception("Unsupported resource type (fopen the directory?)");
            }
        }

        // 'w' -> open for writing, truncate existing files
        if (strpos($mode, "w") !== false) {
            ftruncate($this->_handle, 0);
        }
        // 'a' -> open for appending
        if (strpos($mode, "a") !== false) {
            fseek($this->_handle, 0, SEEK_END);
        }

        return true;
    }

    public function stream_close()
    {
        // todo: locking of connected resource

        if ($this->_handle) {

            if ($this->_handleChanged && !$this->_getClient()->put($this->_url, $this->_handle)) {
                throw new WebDAV_Stream_Exception(sprintf(
                    'failed to update WebDAV resource at %s', $this->_url->getUrl()
                ));
            }

            fclose($this->_handle);
            $this->_handle = null;
        }
    }

    public function stream_stat()
    {
        return $this->_stat;
    }

    public function stream_read($count)
    {
        return fread($this->_handle, $count);
    }

    public function stream_write($buffer)
    {
        $this->_handleChanged = true;

        return fwrite($this->_handle, $buffer);
    }

    public function stream_eof()
    {
        return feof($this->_handle);
    }

    public function stream_tell()
    {
        return ftell($this->_handle);
    }

    public function stream_seek($pos, $whence)
    {
        return fseek($this->_handle, $pos, $whence);
    }

    public function url_stat($url/*, $flags*/)
    {
        $stat = array(
            'mode'          => 0,
            'atime'         => 0,
            'mtime'         => 0
        );

        if ($url = $this->_parseStreamUrl((string) $url)) {
            // issue propfind
            $propfind = new WebDAV_Propfind(WebDAV_Propfind::MODE_PROP);
            $propfind->setProperty('resourcetype');
            $propfind->setProperty('getcontentlength');
            $propfind->setProperty('getlastmodified');

            $propfindResponse = $this->_getClient()->propfind(
                $url, $propfind
            );

            if (207 == $propfindResponse->getResponseCode()) {

                $multistatus = new WebDAV_Multistatus(
                    $propfindResponse->getBodyAsString()
                );

                // in case of directory some servers append it with '/'
                $propstat = false === ($propstat = $multistatus->getHrefPropstat($url->getPart(HTTP_URL::URL_PATH))) ?
                            $multistatus->getHrefPropstat($url->getPart(HTTP_URL::URL_PATH) . '/')
                            : $propstat;

                if (($typeprop = $propstat->getByName('resourcetype', 'DAV:'))) {
                    if ($typeprop->getDomElement()->firstChild) { // resourcetype has subnode
                        if ('collection' == $typeprop->getDomElement()->firstChild->localName &&
                            'DAV:' == $typeprop->getDomElement()->firstChild->namespaceURI) {
                            $stat['mode'] = 040000; // S_IFDIR
                        } else { // have no idea how to parse this resourcetype
                            throw new WebDAV_Stream_Exception("propfind response contains unknown 'resourcetype' node");
                        }
                    } else { // node seems is a file
                        $stat['mode'] = 0100000; // S_IREG
                        if ($sizeprop = $propstat->getByName('getcontentlength', 'DAV:')) {
                            $stat['size'] = intval($sizeprop->getValue());
                        }
                    }

                    if ($mtimeprop = $propstat->getByName('getlastmodified', 'DAV:')) {
                        $attributes = $mtimeprop->getDomElement()->attributes;
                        if ('dateTime.rfc1123' == $attributes->getNamedItem('dt')->nodeValue) {
                            // todo: in whose timezone (ours or theirs) this should be?
                            $date = DateTime::createFromFormat(DateTime::RFC1123, $mtimeprop->getValue());
                            $this->_stat['atime'] = $this->_stat['mtime'] = $date->getTimestamp();
                        } else { // fuck them all
                            $this->_stat['atime'] = $this->_stat['mtime'] = strtotime($mtimeprop->getValue());
                        }
                    }
                } else {
                    throw new WebDAV_Stream_Exception("propfind response does not contains 'resourcetype' node");
                }
            }
        }

        return $stat;
    }

    /* directory iterating methods */

    public function dir_opendir($href)
    {
        if (!$this->_url = $this->_parseStreamUrl($href)) {
            return false;
        }

        // directory should end up with '/' [?: and have no query/fragment]
        $this->_url->setPart(
            HTTP_URL::URL_PATH,
            rtrim($this->_url->getPart('path'), '/') . '/'
        );
        $this->_url->setPart(HTTP_URL::URL_QUERY,       null);
        $this->_url->setPart(HTTP_URL::URL_FRAGMENT,    null);


        $propfind = new WebDAV_Propfind(WebDAV_Propfind::MODE_PROP);
        $propfind->setProperty('resourcetype');

        $response = $this->_getClient()->propfind($this->_url, $propfind, '1');

        if (207 != $response->getResponseCode()) {
            return false;
        }

        $multistatus    = new WebDAV_Multistatus($response->getBodyAsString());
        $propstats      = $multistatus->getPropstats();

        $this->_dirEntries  = array();
        $this->_dirPosition = 0;

        foreach(array_keys($propstats) as $propstatHref) {

            if ($propstatHref == $this->_url->getPart(HTTP_URL::URL_PATH))
                continue; // skip .

            $this->_dirEntries[] = basename($propstatHref);
        }

        return true;
    }

    public function dir_readdir()
    {
        if (!count($this->_dirEntries))
            return false;

        if ($this->_dirPosition >= count($this->_dirEntries))
            return false;

        return $this->_dirEntries[$this->_dirPosition++];
    }

    public function dir_rewinddir()
    {
        $this->_dirPosition = 0;
    }

    public function dir_closedir()
    {
        $this->_dirEntries      = array();
        $this->_dirPosition     = 0;
    }

    /* directory methods*/

    public function mkdir($path)
    {
        if (!$url = $this->_parseStreamUrl($path)) {
            return false;
        }

        return $this->_getClient()->mkcol($url);
    }

    public function rmdir($path)
    {
        // todo check is collection and is empty

        if (!$url = $this->_parseStreamUrl($path)) {
            return false;
        }

        return $this->_getClient()->delete($url);
    }

    /* file methods */

    public function rename($old, $new)
    {
        if (!($oldUrl = $this->_parseStreamUrl($old)) || !($newUrl = $this->_parseStreamUrl($new))) {
            return false;
        }

        return $this->_getClient()->rename($oldUrl, $newUrl);
    }

    public function unlink($path)
    {
        if (!$url = $this->_parseStreamUrl($path)) {
            return false;
        }

        return $this->_getClient()->delete($url);
    }

    /* protected methods */

    protected function _parseStreamUrl($path)
    {
        $url = new HTTP_URL($path);

        switch ($url->getPart(HTTP_URL::URL_SCHEME)) {
            case 'webdav':
                $url->setPart(HTTP_URL::URL_SCHEME, 'http');
                break;
            case 'webdavs':
                $url->setPart(HTTP_URL::URL_SCHEME, 'https');
                break;
            default:
                return false;
                break;
        }

        return $url;
    }

    protected function _getClient()
    {
        if (null !== $this->context) {
            $context = stream_context_get_options(
                $this->context
            );
        } else {
            $context = array();
        }

        return new WebDAV_Client(
            array(
                'transport'     => array(
                    'class'     => self::$_transport,
                    'options'   => array()
                )
            ) + $context
        );
    }
}

stream_wrapper_register('webdav',   'WebDAV_Stream_Wrapper');
stream_wrapper_register('webdavs',  'WebDAV_Stream_Wrapper');