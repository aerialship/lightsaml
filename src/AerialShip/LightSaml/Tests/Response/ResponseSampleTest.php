<?php

namespace AerialShip\LightSaml\Tests\Response;

use AerialShip\LightSaml\ClaimTypes;
use AerialShip\LightSaml\Model\Metadata\EntityDescriptor;
use AerialShip\LightSaml\Model\Protocol\Response;
use AerialShip\LightSaml\Model\XmlDSig\SignatureValidator;
use AerialShip\LightSaml\NameIDPolicy;
use AerialShip\LightSaml\Protocol;


class ResponseSampleTest extends \PHPUnit_Framework_TestCase
{
    function testOne() {
        $destinationURL = 'https://mt.evo.team/simplesaml/module.php/saml/sp/saml2-acs.php/b1';
        $requestID = '_513cb532f91881ffdcf054a573826f831cc1603241';
        $idpEntityID = 'https://B1.bead.loc/adfs/services/trust';
        $spEntityID = 'https://mt.evo.team/simplesaml/module.php/saml/sp/metadata.php/b1';
        $nameID = 'bos@bead.loc';
        $instant = 1382874937;

        $doc = new \DOMDocument();
        $doc->load(__DIR__.'/../../../../../resources/sample/Response/response01.xml');

        $response = new Response();
        $response->loadFromXml($doc->firstChild);

        $this->assertEquals('_c34b38b9-5da6-4ee8-af49-2af20423d8f5', $response->getID());
        $this->assertEquals(Protocol::VERSION_2_0, $response->getVersion());
        $this->assertEquals($instant, $response->getIssueInstant());
        $this->assertEquals($destinationURL, $response->getDestination());
        $this->assertEquals($requestID, $response->getInResponseTo());
        $this->assertEquals($idpEntityID, $response->getIssuer());


        $this->assertNotNull($response->getStatus());
        $this->assertTrue($response->getStatus()->isSuccess());
        $this->assertEquals(Protocol::STATUS_SUCCESS, $response->getStatus()->getCode());


        $arrAssertions = $response->getAllAssertions();
        $this->assertEquals(1, count($arrAssertions));

        $assertion = $arrAssertions[0];

        $this->assertEquals($idpEntityID, $assertion->getIssuer());

        $key = $this->getXmlKey();
        /** @var $signature SignatureValidator */
        $signature = $assertion->getSignature();
        $this->assertNotNull($signature);
        $this->assertTrue($signature instanceof SignatureValidator, get_class($signature));
        $signature->validate($key);

        $this->assertNotNull($assertion->getSubject());
        $this->assertNotNull($assertion->getSubject()->getNameID());
        $this->assertEquals($nameID, $assertion->getSubject()->getNameID()->getValue());
        $this->assertEquals(NameIDPolicy::TRANSIENT, $assertion->getSubject()->getNameID()->getAttribute('Format'));

        $arrSubjectConfirmations = $assertion->getSubject()->getSubjectConfirmations();
        $this->assertEquals(1, count($arrSubjectConfirmations));
        $sc = $arrSubjectConfirmations[0];

        $this->assertEquals(Protocol::CM_BEARER, $sc->getMethod());
        $this->assertNotNull($sc->getData());
        $this->assertEquals($requestID, $sc->getData()->getInResponseTo());
        $this->assertEquals($instant + 5 * 60, $sc->getData()->getNotOnOrAfter());
        $this->assertEquals(0, $sc->getData()->getNotBefore());
        $this->assertEquals($destinationURL, $sc->getData()->getRecipient());

        $this->assertEquals($instant, $assertion->getNotBefore());
        $this->assertEquals($instant+3600, $assertion->getNotOnOrAfter());
        $this->assertEquals(1, count($assertion->getValidAudience()));
        $this->assertTrue(in_array($spEntityID, $assertion->getValidAudience()));

        $this->assertEquals(1, count($assertion->getAllAttributes()));
        $attrCN = $assertion->getAttribute(ClaimTypes::COMMON_NAME);
        $this->assertNotNull($attrCN);
        $this->assertEquals(ClaimTypes::COMMON_NAME, $attrCN->getName());
        $this->assertEquals(1, count($attrCN->getValues()));
        $this->assertEquals($nameID, $attrCN->getFirstValue());

        $this->assertNotNull($assertion->getAuthnStatement());
        $this->assertEquals($instant-1, $assertion->getAuthnStatement()->getAuthnInstant());
        $this->assertEquals('_3ba23925-e43d-4c98-ac99-a05dce99d505', $assertion->getAuthnStatement()->getSessionIndex());
        $this->assertEquals(Protocol::AC_WINDOWS, $assertion->getAuthnStatement()->getAuthnContext());
    }


    private function getXmlKey() {
        $cert = $this->getCertificate();
        $key = new \XMLSecurityKey(\XMLSecurityKey::RSA_SHA1, array('type'=>'public'));
        $key->loadKey($cert->toPem(), false, true);
        return $key;
    }

    /**
     * @return \AerialShip\LightSaml\Security\X509Certificate
     */
    private function getCertificate() {
        $ed = new EntityDescriptor();
        $doc = new \DOMDocument();
        $doc->load(__DIR__.'/../../../../../resources/sample/EntityDescriptor/idp2-ed.xml');
        $ed->loadFromXml($doc->firstChild);
        $arrIdp = $ed->getIdpSsoDescriptors();
        $idp = $arrIdp[0];
        $arrKeys = $idp->findKeyDescriptors('signing');
        $k = $arrKeys[0];
        $cert = $k->getCertificate();
        return $cert;
    }

}
