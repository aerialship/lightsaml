<?php

namespace AerialShip\LightSaml\Tests\Model\Metadata\EntitiesDescriptor;


use AerialShip\LightSaml\Model\Metadata\EntitiesDescriptor;

class EntitiesDescriptorFunctional01Test extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     */
    public function shouldLoadTestshibMetadata()
    {
        $doc = new \DOMDocument();
        $doc->load(__DIR__.'/../../../../../../../resources/sample/EntitiesDescriptor/testshib-providers.xml');

        $ed = new EntitiesDescriptor();
        $ed->loadFromXml($doc->firstChild);

        $arr = $ed->getAllEntityDescriptors();
        $this->assertCount(2, $arr);

        $this->assertEquals('https://idp.testshib.org/idp/shibboleth', $arr[0]->getEntityID());
        $this->assertCount(1, $arr[0]->getAllIdpSsoDescriptors());
        $this->assertCount(0, $arr[0]->getAllSpSsoDescriptors());

        $this->assertEquals('https://sp.testshib.org/shibboleth-sp', $arr[1]->getEntityID());
        $this->assertCount(0, $arr[1]->getAllIdpSsoDescriptors());
        $this->assertCount(1, $arr[1]->getAllSpSsoDescriptors());
    }


} 