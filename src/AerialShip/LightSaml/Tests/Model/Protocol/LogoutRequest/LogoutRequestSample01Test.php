<?php

namespace AerialShip\LightSaml\Tests\LogoutRequest;

use AerialShip\LightSaml\Meta\SerializationContext;
use AerialShip\LightSaml\Meta\SpMeta;
use AerialShip\LightSaml\Model\Protocol\LogoutRequest;
use AerialShip\LightSaml\NameIDPolicy;
use AerialShip\LightSaml\Protocol;
use AerialShip\LightSaml\Tests\CommonHelper;


class LogoutRequestSample01Test extends \PHPUnit_Framework_TestCase
{

    private $issuer = 'https://mt.evo.team/simplesaml/module.php/saml/sp/metadata.php/default-sp';
    private $destination = 'https://b1.bead.loc/adfs/ls/';


    function testOne() {
        $spMeta = new SpMeta();
        $spMeta->setNameIdFormat(NameIDPolicy::PERSISTENT);

        $request = CommonHelper::buildLogoutRequestFromEntityDescriptors(
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

        $context = new SerializationContext();
        $request->getXml($context->getDocument(), $context);
        $this->checkRequestXml($context->getDocument(), $request);

        $request = new LogoutRequest();
        $request->loadFromXml($context->getDocument()->firstChild);
        $this->checkRequestObject($request, $id, $time);
    }

    private function checkRequestObject(LogoutRequest $request, $id, $time) {
        $this->assertEquals($id, $request->getID());
        $this->assertEquals('2.0', $request->getVersion());
        $this->assertEquals($this->destination, $request->getDestination());
        $this->assertEquals($time, $request->getIssueInstant());
        $this->assertEquals($this->issuer, $request->getIssuer());

        $reason = $request->getReason();
        if($reason != null){
            $this->assertStringMatchesFormat('%s', $reason);
        }
        $NameId = $request->getNameID();
        $this->assertInstanceOf('AerialShip\LightSaml\Model\Assertion\NameID', $NameId);
        $this->assertNotEmpty($NameId->getFormat());
    }

    private function checkRequestXml(\DOMDocument $doc, LogoutRequest $request)
    {
        $xpath = new \DOMXPath($doc);
        $xpath->registerNamespace('samlp', Protocol::SAML2);
        $xpath->registerNamespace('saml', Protocol::NS_ASSERTION);

        $list = $xpath->query('/samlp:LogoutRequest');
        $this->assertEquals(1, $list->length);

        /** @var $node \DOMElement */
        $node = $list->item(0);

        $this->assertEquals($request->getReason(), $node->getAttribute('Reason'));
        $this->assertEquals($request->getID(), $node->getAttribute('ID'));
        $this->assertEquals('2.0', $node->getAttribute('Version'));
        $this->assertEquals($this->destination, $node->getAttribute('Destination'));

        $list = $xpath->query('/samlp:LogoutRequest/saml:Issuer');
        $this->assertEquals(1, $list->length);
        $node = $list->item(0);
        $this->assertEquals($this->issuer, $node->textContent);

        $list = $xpath->query('/samlp:LogoutRequest/saml:NameID');
        $this->assertEquals(1, $list->length);
        $node = $list->item(0);
        $this->assertEquals($request->getNameID()->getFormat(), $node->getAttribute('Format'));
        $this->assertEquals($request->getNameID()->getValue(), $node->textContent);
    }
} 