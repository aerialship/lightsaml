<?php

namespace AerialShip\LightSaml\Tests\Signature;

use AerialShip\LightSaml\Meta\SerializationContext;
use AerialShip\LightSaml\Model\XmlDSig\SignatureCreator;
use AerialShip\LightSaml\Model\XmlDSig\SignatureXmlValidator;
use AerialShip\LightSaml\Protocol;
use AerialShip\LightSaml\Security\KeyHelper;
use AerialShip\LightSaml\Security\X509Certificate;


class SignatureCreatorValidatorTest extends \PHPUnit_Framework_TestCase
{
    function testOne() {
        $xml = $this->getSignedXml();
        $this->verifySignature($xml);
    }


    private function verifySignature($xml) {
        $doc = new \DOMDocument();
        $doc->loadXML($xml);

        $xpath = new \DOMXPath($doc);
        $xpath->registerNamespace('ds', Protocol::NS_XMLDSIG);

        $list = $xpath->query('/root/ds:Signature');
        $this->assertEquals(1, $list->length);

        /** @var $signatureNode \DOMElement */
        $signatureNode = $list->item(0);

        $signatureValidator = new SignatureXmlValidator();
        $signatureValidator->loadFromXml($signatureNode);


        $certificate = new X509Certificate();
        $certificate->loadFromFile(__DIR__.'/../../../../../resources/sample/Certificate/saml.crt');

        $key = KeyHelper::createPublicKey($certificate);

        $ok = $signatureValidator->validate($key);

        $this->assertTrue($ok);
    }


    private function getSignedXml() {
        $doc = new \DOMDocument();
        $doc->appendChild($doc->createElement('root'));
        /** @var $root \DOMElement */
        $root = $doc->firstChild;
        $root->setAttribute('foo', 'bar');

        $other = $doc->createElement('other');
        $root->appendChild($other);
        $child = $doc->createElement('child', 'something');
        $other->appendChild($child);

        $certificate = new X509Certificate();
        $certificate->loadFromFile(__DIR__.'/../../../../../resources/sample/Certificate/saml.crt');

        $key = new \XMLSecurityKey(\XMLSecurityKey::RSA_SHA1, array('type'=>'private'));
        $key->loadKey(__DIR__.'/../../../../../resources/sample/Certificate/saml.pem', true);

        $signatureCreator = new SignatureCreator();
        $signatureCreator->setCertificate($certificate);
        $signatureCreator->setXmlSecurityKey($key);

        $context = new SerializationContext($doc);
        $signatureCreator->getXml($root, $context);

        $xml = $doc->saveXML();

        //file_put_contents(__DIR__.'/../../../../../resources/sample/foo.xml', $xml);
        //print "\n\n".$xml."\n\n";

        return $xml;

    }


}