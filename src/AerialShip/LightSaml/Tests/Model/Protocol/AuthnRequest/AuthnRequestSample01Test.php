<?php

namespace AerialShip\LightSaml\Tests\Model\Protocol\AuthnRequest;

use AerialShip\LightSaml\Bindings;
use AerialShip\LightSaml\Meta\SerializationContext;
use AerialShip\LightSaml\Meta\SpMeta;
use AerialShip\LightSaml\Model\Protocol\AuthnRequest;
use AerialShip\LightSaml\NameIDPolicy;
use AerialShip\LightSaml\Protocol;
use AerialShip\LightSaml\Tests\CommonHelper;


class AuthnRequestSample01Test extends \PHPUnit_Framework_TestCase
{
    private $issuer = 'https://mt.evo.team/simplesaml/module.php/saml/sp/metadata.php/default-sp';
    private $destination = 'https://b1.bead.loc/adfs/ls/';
    private $ascURL = 'https://mt.evo.team/simplesaml/module.php/saml/sp/saml2-acs.php/default-sp';
    private $protocolBinding = Bindings::SAML2_HTTP_POST;
    private $nameIDPolicyFormat = NameIDPolicy::PERSISTENT;


    function testOne() {
        $spMeta = new SpMeta();
        $spMeta->setNameIdFormat(NameIDPolicy::PERSISTENT);
        $request = CommonHelper::buildAuthnRequestFromEntityDescriptors(
            __DIR__.'/../../../../../../../resources/sample/EntityDescriptor/sp-ed2.xml',
            __DIR__.'/../../../../../../../resources/sample/EntityDescriptor/idp2-ed.xml',
            $spMeta
        );

        $id = $request->getID();
        $this->assertNotEmpty($id);
        $this->assertEquals(43, strlen($id));

        $time = $request->getIssueInstant();
        $this->assertNotEmpty($time);
        $this->assertLessThan(2, abs(time()-$time));

        $this->checkRequestObject($request, $id, $time);

        // serialize to XML Document and check xml
        $context = new SerializationContext();
        $request->getXml($context->getDocument(), $context);
        $this->checkRequestXml($context->getDocument(), $id);

        // Deserialize new request out of xml
        $request = new AuthnRequest();
        $request->loadFromXml($context->getDocument()->firstChild);
        $this->checkRequestObject($request, $id, $time);

        // serialize again to xml and check xml
        $context = new SerializationContext();
        $request->getXml($context->getDocument(), $context);
        $this->checkRequestXml($context->getDocument(), $id);
    }

    private function checkRequestObject(AuthnRequest $request, $id, $time) {
        $this->assertEquals($id, $request->getID());
        $this->assertEquals('2.0', $request->getVersion());
        $this->assertEquals($this->destination, $request->getDestination());
        $this->assertEquals($this->ascURL, $request->getAssertionConsumerServiceURL());
        $this->assertEquals($this->protocolBinding, $request->getProtocolBinding());
        $this->assertEquals($time, $request->getIssueInstant());

        $this->assertEquals($this->issuer, $request->getIssuer());
        $this->assertEquals($this->nameIDPolicyFormat, $request->getNameIdPolicyFormat());
        $this->assertTrue($request->getNameIdPolicyAllowCreate());
        $this->assertFalse($request->getSuppressNameIdPolicy());
    }


    private function checkRequestXml(\DOMDocument $doc, $id) {
        //$xml = $doc->saveXML();
        //print "\n\n$xml\n\n";

        $xpath = new \DOMXPath($doc);
        $xpath->registerNamespace('samlp', Protocol::SAML2);
        $xpath->registerNamespace('saml', Protocol::NS_ASSERTION);

        $list = $xpath->query('/samlp:AuthnRequest');
        $this->assertEquals(1, $list->length);

        /** @var $node \DOMElement */
        $node = $list->item(0);

        $this->assertEquals($id, $node->getAttribute('ID'));
        $this->assertEquals('2.0', $node->getAttribute('Version'));
        $this->assertEquals($this->destination, $node->getAttribute('Destination'));
        $this->assertEquals($this->ascURL, $node->getAttribute('AssertionConsumerServiceURL'));
        $this->assertEquals($this->protocolBinding, $node->getAttribute('ProtocolBinding'));

        $list = $xpath->query('/samlp:AuthnRequest/saml:Issuer');
        $this->assertEquals(1, $list->length);
        /** @var $node \DOMElement */
        $node = $list->item(0);
        $this->assertEquals($this->issuer, $node->textContent);

        $list = $xpath->query('/samlp:AuthnRequest/samlp:NameIDPolicy');
        $this->assertEquals(1, $list->length);
        /** @var $node \DOMElement */
        $node = $list->item(0);
        $this->assertEquals($this->nameIDPolicyFormat, $node->getAttribute('Format'));
        $this->assertEquals('true', $node->getAttribute('AllowCreate'));
    }

}