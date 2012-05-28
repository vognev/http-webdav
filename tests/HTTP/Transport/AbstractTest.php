<?php
require_once 'PHPUnit/Framework/TestCase.php';
require_once 'HTTP/Transport/Abstract.php';

class HTTP_Transport_AbstractTest extends PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        require_once 'HTTP/Transport/AbstractLoaded.php';

        HTTP_Transport_Abstract::factory(
            'AbstractLoaded', array()
        );

        HTTP_Transport_Abstract::factory(
            'AbstractUnloaded', array()
        );

        HTTP_Transport_Abstract::factory(
            'AbstractAbsent', array()
        );
    }

    public function testOptions()
    {
        $options    = array('test' => 'test');
        /** @var $mock HTTP_Transport_Abstract */
        $mock       = $this->getMockForAbstractClass('HTTP_Transport_Abstract', array($options));

        $this->assertEquals($options, $mock->getOptions());
    }
}
