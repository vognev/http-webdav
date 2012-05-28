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
}