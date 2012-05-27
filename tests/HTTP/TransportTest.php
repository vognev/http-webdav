<?php

require_once 'PHPUnit/Framework/TestCase.php';

abstract class HTTP_TransportTest extends PHPUnit_Framework_TestCase
{
    protected $_transportClass;

    public function testCanSetOptions()
    {
        $transportOptions = array(
            'foo'       => 'bar'
        );

        /** @var $transport HTTP_Transport_Abstract */
        $transport = new $this->_transportClass($transportOptions);

        $this->assertEquals($transportOptions, $transport->getOptions());
    }

    public function testCanIssueGet()
    {
        /** @var $transport HTTP_Transport_Abstract */
        $transport  = new $this->_transportClass();
        $getOptions = array('foo' => 'bar', 'test' => array(1, 2, 3));

        $url        = new HTTP_URL(TEST_TRANSPORT_CURL_BASEURL);
        $url->setPart(HTTP_URL::URL_PATH, '/get.php');
        $url->setPart(HTTP_URL::URL_QUERY, http_build_query($getOptions));

        $request    = new HTTP_Request($url);

        $response   = $transport->execute($request);

        $this->assertEquals($request, $response->getRequest());
        $this->assertInternalType('resource', $response->getBody());

        //$gotOptions = unserialize(stream_get_contents($response->getBody()));
        //$this->assertEquals($getOptions, $gotOptions);
    }
}