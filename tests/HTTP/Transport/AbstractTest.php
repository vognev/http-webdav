<?php
require_once 'PHPUnit/Framework/TestCase.php';

class HTTP_Transport_AbstractTest extends PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        HTTP\Transport::factory(
            'AbstractTransport', array()
        );
    }

    public function testOptions()
    {
        $options    = array('test' => 'test');
        /** @var $mock HTTP\Transport */
        $mock       = $this->getMockForAbstractClass('\HTTP\Transport', array($options));

        $this->assertEquals($options, $mock->getOptions());
    }
}
