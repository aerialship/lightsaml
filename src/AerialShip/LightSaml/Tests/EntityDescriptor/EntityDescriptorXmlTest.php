<?php

namespace AerialShip\LightSaml\Tests\EntityDescriptor;

use AerialShip\LightSaml\Binding;
use AerialShip\LightSaml\EntityDescriptor\EntityDescriptor;
use AerialShip\LightSaml\EntityDescriptor\SP\AssertionConsumerServiceItem;
use AerialShip\LightSaml\EntityDescriptor\SP\SingleLogoutServiceItem;
use AerialShip\LightSaml\EntityDescriptor\SP\SpSsoDescriptor;
use AerialShip\LightSaml\Protocol;


class EntityDescriptorXmlTest extends \PHPUnit_Framework_TestCase
{

    function testOne() {
        $entityID = 'http://example.com';
        $locationLogout = 'http://example.com/logout';
        $locationLogin = 'http://example.com/login';
        $ed = new EntityDescriptor(
            $entityID,
            array(
                new SpSsoDescriptor(
                    array(
                        new SingleLogoutServiceItem(Binding::SAML2_HTTP_REDIRECT, $locationLogout),
                        new AssertionConsumerServiceItem(Binding::SAML2_HTTP_POST, $locationLogin, 0),
                        new AssertionConsumerServiceItem(Binding::SAML2_HTTP_ARTIFACT, $locationLogin, 1)
                    )
                )
            )
        );

        $xml = $ed->toXmlString();
        $document = new \DOMDocument();
        $document->loadXML($xml);
        /** @var $root \DOMElement */
        $root = $document->firstChild;

        $this->checkXml($root, $entityID, $locationLogout, $locationLogin);
        $this->checkDeserializaton($root, $entityID, $locationLogout, $locationLogin);
    }


    private function checkXml(\DOMElement $root, $entityID, $locationLogout, $locationLogin) {

        $this->assertEquals(Protocol::NS_METADATA, $root->namespaceURI);
        $this->assertEquals('EntityDescriptor', $root->localName);
        $this->assertTrue($root->hasAttribute('entityID'));
        $this->assertEquals($entityID, $root->getAttribute('entityID'));
        $this->assertEquals(3, $root->childNodes->length);

        $this->assertEquals('', trim($root->childNodes->item(0)->nodeValue));
        $this->assertEquals('', trim($root->childNodes->item(2)->nodeValue));

        /** @var $sp \DOMElement */
        $sp = $root->childNodes->item(1);
        $this->assertEquals(Protocol::NS_METADATA, $sp->namespaceURI);
        $this->assertEquals('SPSSODescriptor', $sp->localName);
        $this->assertTrue($sp->hasAttribute('protocolSupportEnumeration'));
        $this->assertEquals(Protocol::SAML2, $sp->getAttribute('protocolSupportEnumeration'));
        $this->assertEquals(7, $sp->childNodes->length);
        $this->assertEquals('', trim($sp->childNodes->item(0)->nodeValue));
        $this->assertEquals('', trim($sp->childNodes->item(2)->nodeValue));
        $this->assertEquals('', trim($sp->childNodes->item(4)->nodeValue));
        $this->assertEquals('', trim($sp->childNodes->item(6)->nodeValue));

        /** @var $logout \DOMElement */
        $logout = $sp->childNodes->item(1);
        $this->checkSpItemXml($logout, 'SingleLogoutService', Binding::SAML2_HTTP_REDIRECT, $locationLogout, null);

        /** @var $logout \DOMElement */
        $as1 = $sp->childNodes->item(3);
        $this->checkSpItemXml($as1, 'AssertionConsumerService', Binding::SAML2_HTTP_POST, $locationLogin, 0);

        /** @var $logout \DOMElement */
        $as2 = $sp->childNodes->item(5);
        $this->checkSpItemXml($as2, 'AssertionConsumerService', Binding::SAML2_HTTP_ARTIFACT, $locationLogin, 1);
    }


    private function checkSpItemXml(\DOMElement $element, $name, $binding, $location, $index) {
        $this->assertEquals(Protocol::NS_METADATA, $element->namespaceURI);
        $this->assertEquals($name, $element->localName);
        $this->assertTrue($element->hasAttribute('Binding'));
        $this->assertEquals($binding, $element->getAttribute('Binding'));
        $this->assertTrue($element->hasAttribute('Location'));
        $this->assertEquals($location, $element->getAttribute('Location'));
        if ($index !== null) {
            $this->assertTrue($element->hasAttribute('index'));
            $this->assertEquals($index, $element->getAttribute('index'));
        } else {
            $this->assertFalse($element->hasAttribute('index'));
        }
    }


    private function checkDeserializaton(\DOMElement $root, $entityID, $locationLogout, $locationLogin) {
        $ed = new EntityDescriptor();
        $arr = $ed->loadXml($root);
        $this->assertEquals(0, count($arr));

        $this->assertEquals($entityID, $ed->getEntityID());

        $items = $ed->getItems();
        $this->assertEquals(1, count($items));
        $this->assertTrue($items[0] instanceof SpSsoDescriptor);

        $sp = $ed->getSpSsoItem();
        $this->assertNotNull($sp);
        $this->assertTrue($sp instanceof SpSsoDescriptor);

        $this->assertEquals(Protocol::SAML2, $sp->getProtocolSupportEnumeration());

        $items = $sp->getItems();
        $this->assertEquals(3, count($items), print_r($items, true));

        $logout = $sp->getSingleLogoutItem();
        $this->assertNotNull($logout);
        $this->assertEquals(Binding::SAML2_HTTP_REDIRECT, $logout->getBinding());
        $this->assertEquals($locationLogout, $logout->getLocation());

        $arr = $sp->getAllAssertionConsumerItems();
        $this->assertEquals(2, count($arr));

        $as1 = $sp->getAssertionConsumerItemForBinding(Binding::SAML2_HTTP_POST);
        $this->assertNotNull($as1);
        $this->assertEquals(Binding::SAML2_HTTP_POST, $as1->getBinding());
        $this->assertEquals($locationLogin, $as1->getLocation());
        $this->assertEquals(0, $as1->getIndex());

        $as2 = $sp->getAssertionConsumerItemForBinding(Binding::SAML2_HTTP_ARTIFACT);
        $this->assertNotNull($as2);
        $this->assertEquals(Binding::SAML2_HTTP_ARTIFACT, $as2->getBinding());
        $this->assertEquals($locationLogin, $as2->getLocation());
        $this->assertEquals(1, $as2->getIndex());

    }

}