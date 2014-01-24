<?php

namespace AerialShip\LightSaml\Tests\Model\XmlDSig\Signature;

use AerialShip\LightSaml\Model\XmlDSig\SignatureXmlValidator;
use AerialShip\LightSaml\Protocol;
use AerialShip\LightSaml\Security\KeyHelper;
use AerialShip\LightSaml\Security\X509Certificate;


class SignatureSample01Test extends \PHPUnit_Framework_TestCase
{

    function testOne() {
        $doc = new \DOMDocument();
        $doc->load(__DIR__.'/../../../../../../../resources/sample/Response/response01.xml');

        $xpath = new \DOMXPath($doc);
        $xpath->registerNamespace('samlp', Protocol::SAML2);
        $xpath->registerNamespace('ds', Protocol::NS_XMLDSIG);
        $xpath->registerNamespace('a', Protocol::NS_ASSERTION);

        $list = $xpath->query('/samlp:Response/a:Assertion/ds:Signature');
        $this->assertEquals(1, $list->length);
        /** @var $signatureNode \DOMElement */
        $signatureNode = $list->item(0);

        $signatureValidator = new SignatureXmlValidator();
        $signatureValidator->loadFromXml($signatureNode);

        $list = $xpath->query('./ds:KeyInfo/ds:X509Data/ds:X509Certificate', $signatureNode);
        $this->assertEquals(1, $list->length);
        /** @var $signatureNode \DOMElement */
        $certificateDataNode = $list->item(0);

        $certData = $certificateDataNode->textContent;
        $certificate = new X509Certificate();
        $certificate->setData($certData);
        $key = KeyHelper::createPublicKey($certificate);

        $ok = $signatureValidator->validate($key);
        $this->assertTrue($ok);
    }

}