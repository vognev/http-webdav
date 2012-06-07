<?php

namespace HTTP\Transport;

class Curl extends \HTTP\Transport
{
    public function __construct(array $options = array())
    {
        // @codeCoverageIgnoreStart
        if (!function_exists('curl_init')) {
            throw new Exception("cURL Transport requires cURL extension");
        }
        // @codeCoverageIgnoreEnd

        parent::__construct($options);
    }

    public function execute(\HTTP\Request $request)
    {
        $response   = new \HTTP\Response();
        $response->setRequest($request);

        $ch         = curl_init($request->getUrl()->getUrl());

        $hh = fopen('php://temp', 'w+');
        $fh = fopen('php://temp', 'w+');

        $options            = $this->getOptions();

        if(is_resource($request->getBody())) {
            $body = $request->getBody();
            fseek($body, 0, SEEK_END);
            $size = ftell($body);
            fseek($body, 0, SEEK_SET);

            $options[CURLOPT_INFILE]                = $body;
            $options[CURLOPT_INFILESIZE]            = $size;

        } else {
            $options[CURLOPT_POSTFIELDS]            = $request->getBodyAsString();
        }

        switch($request->getMethod()) {
            case 'PUT':
                $options[CURLOPT_PUT] = true;
                break;
            case 'POST':
                $options[CURLOPT_POST] = true;
                break;
            case 'HEAD':
                $options[CURLOPT_NOBODY] = true;
                break;
            default:
                $options[CURLOPT_CUSTOMREQUEST] = $request->getMethod();
                break;
        }

        $options += array(
            CURLOPT_RETURNTRANSFER          => false,
            CURLOPT_FOLLOWLOCATION          => false,
            CURLOPT_HEADER                  => false,
            CURLOPT_FAILONERROR             => false,
            CURLOPT_HTTPHEADER              => $request->getHeaders(),
            CURLOPT_FILE                    => $fh,
            CURLOPT_WRITEHEADER             => $hh
        );

        curl_setopt_array($ch, $options);

        if (!$r = curl_exec($ch)) {
            throw new Exception(curl_error($ch));
        }

        rewind($hh); rewind($fh);

        $response->setResponseCode(curl_getinfo($ch, CURLINFO_HTTP_CODE));
        $response->setBody($fh);

        $_hp = new \HTTP\HeadersParser($hh);

        foreach($_hp as $name => $value) {
            $response->setHeader($name, $value);
        }

        fclose($hh);
        curl_close($ch);

        return $response;
    }
}