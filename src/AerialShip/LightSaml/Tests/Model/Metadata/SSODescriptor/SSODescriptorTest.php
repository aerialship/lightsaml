<?php

namespace AerialShip\LightSaml\Tests\Model\Metadata\SSODescriptor;

use AerialShip\LightSaml\Model\Metadata\KeyDescriptor;

class SSODescriptorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeAbstract()
    {
        $rc = new \ReflectionClass('AerialShip\LightSaml\Model\Metadata\SSODescriptor');
        $this->assertTrue($rc->isAbstract());
    }

    /**
     * @test
     */
    public function shouldImplementGetXmlInterface()
    {
        $rc = new \ReflectionClass('AerialShip\LightSaml\Model\Metadata\SSODescriptor');
        $this->assertTrue($rc->implementsInterface('AerialShip\LightSaml\Meta\GetXmlInterface'));
    }

    /**
     * @test
     */
    public function shouldImplementLoadFromXmlInterface()
    {
        $rc = new \ReflectionClass('AerialShip\LightSaml\Model\Metadata\SSODescriptor');
        $this->assertTrue($rc->implementsInterface('AerialShip\LightSaml\Meta\LoadFromXmlInterface'));
    }

    /**
     * @test
     */
    public function shouldReturnAllKeyDescriptorsWhenFindKeyDescriptorsCalledWithNullArgument()
    {
        $mock = $this->getSSODescriptorMock();
        $mock->addKeyDescriptor($kd1 = new KeyDescriptor());
        $mock->addKeyDescriptor($kd2 = new KeyDescriptor(KeyDescriptor::USE_SIGNING));
        $mock->addKeyDescriptor($kd3 = new KeyDescriptor(KeyDescriptor::USE_ENCRYPTION));

        $arr = $mock->findKeyDescriptors(null);
        $this->assertCount(3, $arr);
        $this->assertContainsOnlyInstancesOf('AerialShip\LightSaml\Model\Metadata\KeyDescriptor', $arr);
        $this->assertSame($kd1, $arr[0]);
        $this->assertSame($kd2, $arr[1]);
        $this->assertSame($kd3, $arr[2]);
    }

    /**
     * @test
     */
    public function shouldReturnSignAndNullUseKeyDescriptorsWhenFindKeyDescriptorsCalledWithSignArgument()
    {
        $mock = $this->getSSODescriptorMock();
        $mock->addKeyDescriptor($kd1 = new KeyDescriptor());
        $mock->addKeyDescriptor($kd2 = new KeyDescriptor(KeyDescriptor::USE_SIGNING));
        $mock->addKeyDescriptor($kd3 = new KeyDescriptor(KeyDescriptor::USE_ENCRYPTION));

        $arr = $mock->findKeyDescriptors(KeyDescriptor::USE_SIGNING);
        $this->assertCount(2, $arr);
        $this->assertContainsOnlyInstancesOf('AerialShip\LightSaml\Model\Metadata\KeyDescriptor', $arr);
        $this->assertSame($kd1, $arr[0]);
        $this->assertSame($kd2, $arr[1]);
    }

    /**
     * @test
     */
    public function shouldReturnEncryptionAndNullUseKeyDescriptorsWhenFindKeyDescriptorsCalledWithEncryptionArgument()
    {
        $mock = $this->getSSODescriptorMock();
        $mock->addKeyDescriptor($kd1 = new KeyDescriptor());
        $mock->addKeyDescriptor($kd2 = new KeyDescriptor(KeyDescriptor::USE_SIGNING));
        $mock->addKeyDescriptor($kd3 = new KeyDescriptor(KeyDescriptor::USE_ENCRYPTION));

        $arr = $mock->findKeyDescriptors(KeyDescriptor::USE_ENCRYPTION);
        $this->assertCount(2, $arr);
        $this->assertContainsOnlyInstancesOf('AerialShip\LightSaml\Model\Metadata\KeyDescriptor', $arr);
        $this->assertSame($kd1, $arr[0]);
        $this->assertSame($kd3, $arr[1]);
    }



    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\AerialShip\LightSaml\Model\Metadata\SSODescriptor
     */
    private function getSSODescriptorMock()
    {
        return $this->getMock('AerialShip\LightSaml\Model\Metadata\SSODescriptor', array('getXmlNodeName'));
    }

} 