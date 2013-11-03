<?php

namespace AerialShip\LightSaml\Tests\AuthnRequest;

use AerialShip\LightSaml\Binding;
use AerialShip\LightSaml\Meta\AuthnRequestBuilder;
use AerialShip\LightSaml\Meta\SpMeta;
use AerialShip\LightSaml\Model\Protocol\AuthnRequest;
use AerialShip\LightSaml\Model\Metadata\EntityDescriptor;
use AerialShip\LightSaml\NameIDPolicy;
use AerialShip\LightSaml\Protocol;


class AuthnRequestTest extends \PHPUnit_Framework_TestCase
{
    private $issuer = 'https://mt.evo.team/simplesaml/module.php/saml/sp/metadata.php/default-sp';
    private $destination = 'https://B1.bead.loc/adfs/services/trust';
    private $ascURL = 'https://mt.evo.team/simplesaml/module.php/saml/sp/saml2-acs.php/default-sp';
    private $protocolBinding = Binding::SAML2_HTTP_POST;
    private $nameIDPolicyFormat = NameIDPolicy::PERSISTENT;


    function testOne() {
        $doc = new \DOMDocument();
        $doc->load(__DIR__.'/../../../../../resources/sample/EntityDescriptor/idp2-ed.xml');
        $edIDP = new EntityDescriptor();
        $edIDP->loadFromXml($doc->firstChild);

        $doc = new \DOMDocument();
        $doc->load(__DIR__.'/../../../../../resources/sample/EntityDescriptor/sp-ed2.xml');
        $edSP = new EntityDescriptor();
        $edSP->loadFromXml($doc->firstChild);

        $spMeta = new SpMeta();
        $spMeta->setNameIdFormat(NameIDPolicy::PERSISTENT);

        $builder = new AuthnRequestBuilder($edSP, $edIDP, $spMeta);
        $request = $builder->build();

        $id = $request->getID();
        $this->assertNotEmpty($id);
        $this->assertEquals(43, strlen($id));

        $time = $request->getIssueInstant();
        $this->assertNotEmpty($time);
        $this->assertLessThan(1, abs(time()-$time));

        $this->checkRequestObject($request, $id, $time);

        // serialize to XML Document and check xml
        $doc = new \DOMDocument();
        $request->getXml($doc);
        $this->checkRequestXml($doc, $id);

        // Deserialize new request out of xml
        $request = new AuthnRequest();
        $request->loadFromXml($doc->firstChild);
        $this->checkRequestObject($request, $id, $time);

        // serialize again to xml and check xml
        $doc = new \DOMDocument();
        $request->getXml($doc);
        $this->checkRequestXml($doc, $id);
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