<?php

namespace AerialShip\LightSaml\Tests\Model\Metadata\EntitiesDescriptor;

use AerialShip\LightSaml\Meta\SerializationContext;
use AerialShip\LightSaml\Model\Metadata\EntitiesDescriptor;
use AerialShip\LightSaml\Model\Metadata\EntityDescriptor;
use AerialShip\LightSaml\Protocol;

class EntitiesDescriptorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementGetXmlInterface()
    {
        $rc = new \ReflectionClass('AerialShip\LightSaml\Model\Metadata\EntitiesDescriptor');
        $rc->implementsInterface('AerialShip\LightSaml\Meta\GetXmlInterface');
    }

    /**
     * @test
     */
    public function shouldImplementLoadFromXmlInterface()
    {
        $rc = new \ReflectionClass('AerialShip\LightSaml\Model\Metadata\EntitiesDescriptor');
        $rc->implementsInterface('AerialShip\LightSaml\Meta\LoadFromXmlInterface');
    }

    /**
     * @test
     */
    public function shouldSetValidStringToValidUntil()
    {
        $ed = new EntitiesDescriptor();
        $ed->setValidUntil('2013-10-27T11:55:37.035Z');
    }

    /**
     * @test
     */
    public function shouldSetPositiveIntToValidUntil()
    {
        $ed = new EntitiesDescriptor();
        $ed->setValidUntil(123456);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function shouldThrowOnSetInvalidStringToValidUntil()
    {
        $ed = new EntitiesDescriptor();
        $ed->setValidUntil('something');
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function shouldThrowOnSetNegativeIntToValidUntil()
    {
        $ed = new EntitiesDescriptor();
        $ed->setValidUntil(-1);
    }

    /**
     * @test
     */
    public function shouldSetValidPeriodStringToCacheDuration()
    {
        $ed = new EntitiesDescriptor();
        $ed->setCacheDuration('P3D');
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function shouldThrowOnInvalidPeriodStringSetToCacheDuration()
    {
        $ed = new EntitiesDescriptor();
        $ed->setCacheDuration('83D2Y');
    }


    /**
     * @test
     */
    public function shouldAddItemEntitiesDescriptor()
    {
        $ed = new EntitiesDescriptor();
        $ed->addItem(new EntitiesDescriptor());
    }

    /**
     * @test
     */
    public function shouldAddItemEntityDescriptor()
    {
        $ed = new EntitiesDescriptor();
        $ed->addItem(new EntityDescriptor());
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function shouldThrowOnInvalidObjectTypeGivenToAddItem()
    {
        $ed = new EntitiesDescriptor();
        $ed->addItem(new \stdClass());
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function shouldThrowOnArrayGivenToAddItem()
    {
        $ed = new EntitiesDescriptor();
        $ed->addItem(array());
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function shouldThrowOnStringGivenToAddItem()
    {
        $ed = new EntitiesDescriptor();
        $ed->addItem('foo');
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function shouldThrowOnIntGivenToAddItem()
    {
        $ed = new EntitiesDescriptor();
        $ed->addItem(123);
    }


    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function shouldThrowWhenItselfGivenToAddItem()
    {
        $ed = new EntitiesDescriptor();
        $ed->addItem($ed);
    }

    /**
     * @test
     */
    public function shouldContainsItemWork()
    {
        $o1 = new EntitiesDescriptor();
        $o2 = new EntityDescriptor('ed1');
        $o3 = new EntitiesDescriptor();
        $o4 = new EntityDescriptor('ed2');

        $x1 = new EntitiesDescriptor();
        $x2 = new EntityDescriptor('ed3');

        $o1->addItem($o2);
        $o1->addItem($o3);
        $o3->addItem($o4);

        $this->assertTrue($o1->containsItem($o2));
        $this->assertTrue($o1->containsItem($o3));
        $this->assertTrue($o1->containsItem($o4));
        $this->assertFalse($o1->containsItem($x1));
        $this->assertFalse($o1->containsItem($x2));

        $this->assertTrue($o3->containsItem($o4));
        $this->assertFalse($o3->containsItem($o1));
        $this->assertFalse($o3->containsItem($o2));
        $this->assertFalse($o3->containsItem($x1));
        $this->assertFalse($o3->containsItem($x2));
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function shouldThrowWhenCircularReferenceDetectedInAddItem()
    {
        $esd1 = new EntitiesDescriptor();
        $esd1->addItem(new EntityDescriptor('ed1'));
        $esd1->addItem(new EntityDescriptor('ed2'));

        $esd2 = new EntitiesDescriptor();
        $esd2->addItem(new EntityDescriptor('ed3'));
        $esd1->addItem($esd2);

        $esd3 = new EntitiesDescriptor();
        $esd3->addItem(new EntityDescriptor('ed4'));
        $esd2->addItem($esd3);

        $esd3->addItem($esd1);
    }

    /**
     * @test
     */
    public function shouldReturnRecursivelyAllEntityDescriptors()
    {
        $esd1 = new EntitiesDescriptor();
        $esd1->addItem(new EntityDescriptor('ed1'));
        $esd1->addItem(new EntityDescriptor('ed2'));

        $esd2 = new EntitiesDescriptor();
        $esd2->addItem(new EntityDescriptor('ed3'));
        $esd1->addItem($esd2);

        $esd3 = new EntitiesDescriptor();
        $esd3->addItem(new EntityDescriptor('ed4'));
        $esd2->addItem($esd3);

        $all = $esd1->getAllEntityDescriptors();
        $this->assertCount(4, $all);
        $this->assertContainsOnlyInstancesOf('AerialShip\LightSaml\Model\Metadata\EntityDescriptor', $all);
        $this->assertEquals('ed1', $all[0]->getEntityID());
        $this->assertEquals('ed2', $all[1]->getEntityID());
        $this->assertEquals('ed3', $all[2]->getEntityID());
        $this->assertEquals('ed4', $all[3]->getEntityID());
    }


    /**
     * @test
     */
    public function shouldGetXml()
    {
        $esd = new EntitiesDescriptor();
        $esd->addItem(new EntityDescriptor('ed1'));
        $esd->addItem(new EntityDescriptor('ed2'));

        $esd2 = new EntitiesDescriptor();
        $esd2->addItem(new EntityDescriptor('ed3'));
        $esd->addItem($esd2);

        $ctx = new SerializationContext();
        $esd->getXml($ctx->getDocument(), $ctx);

        $xpath = new \DOMXPath($ctx->getDocument());
        $xpath->registerNamespace('md', Protocol::NS_METADATA);

        $this->assertEquals(1, $xpath->query('/md:EntitiesDescriptor')->length);
        $this->assertEquals(2, $xpath->query('/md:EntitiesDescriptor/md:EntityDescriptor')->length);
        $this->assertEquals(1, $xpath->query('/md:EntitiesDescriptor/md:EntityDescriptor[@entityID="ed1"]')->length);
        $this->assertEquals(1, $xpath->query('/md:EntitiesDescriptor/md:EntityDescriptor[@entityID="ed2"]')->length);
        $this->assertEquals(1, $xpath->query('/md:EntitiesDescriptor/md:EntitiesDescriptor')->length);
        $this->assertEquals(1, $xpath->query('/md:EntitiesDescriptor/md:EntitiesDescriptor/md:EntityDescriptor[@entityID="ed3"]')->length);
    }


    /**
     * @test
     */
    public function shouldLoadXml()
    {
        $xml = '<?xml version="1.0"?>
<md:EntitiesDescriptor ID="esd1" Name="first" validUntil="2013-10-27T11:55:37.035Z" cacheDuration="P1D" xmlns:md="urn:oasis:names:tc:SAML:2.0:metadata">
    <md:EntityDescriptor entityID="ed1"/>
    <md:EntityDescriptor entityID="ed2"/>
    <md:EntitiesDescriptor ID="esd2" Name="second">
        <md:EntityDescriptor entityID="ed3"/>
    </md:EntitiesDescriptor>
</md:EntitiesDescriptor>';
        $doc = new \DOMDocument();
        $doc->loadXML($xml);

        $esd = new EntitiesDescriptor();
        $esd->loadFromXml($doc->firstChild);

        $this->assertEquals('esd1', $esd->getId());
        $this->assertEquals('first', $esd->getName());
        $this->assertEquals(1382874937, $esd->getValidUntil());
        $this->assertEquals('P1D', $esd->getCacheDuration());

        $items = $esd->getAllItems();
        $this->assertCount(3, $items);
        $this->assertInstanceOf('AerialShip\LightSaml\Model\Metadata\EntityDescriptor', $items[0]);
        $this->assertInstanceOf('AerialShip\LightSaml\Model\Metadata\EntityDescriptor', $items[1]);
        $this->assertInstanceOf('AerialShip\LightSaml\Model\Metadata\EntitiesDescriptor', $items[2]);
    }

}
