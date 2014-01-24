<?php

namespace AerialShip\LightSaml\Model\Metadata;

use AerialShip\LightSaml\Error\InvalidXmlException;
use AerialShip\LightSaml\Meta\GetXmlInterface;
use AerialShip\LightSaml\Meta\LoadFromXmlInterface;
use AerialShip\LightSaml\Meta\SerializationContext;
use AerialShip\LightSaml\Meta\XmlRequiredAttributesTrait;
use AerialShip\LightSaml\Protocol;
use AerialShip\LightSaml\Security\X509Certificate;


class KeyDescriptor implements GetXmlInterface, LoadFromXmlInterface
{
    use XmlRequiredAttributesTrait;


    const USE_SIGNING = 'signing';
    const USE_ENCRYPTION = 'encryption';


    /** @var string */
    protected $use;

    /** @var X509Certificate */
    private $certificate;



    function __construct($use = null, X509Certificate $certificate = null) {
        $this->use = $use;
        $this->certificate = $certificate;
    }


    /**
     * @param string $use
     * @throws \InvalidArgumentException
     */
    public function setUse($use) {
        $use = trim($use);
        if ($use != '' && $use != self::USE_ENCRYPTION && $use != self::USE_SIGNING) {
            throw new \InvalidArgumentException("Invalid use value: $use");
        }
        $this->use = $use;
    }

    /**
     * @return string
     */
    public function getUse() {
        return $this->use;
    }


    /**
     * @param X509Certificate $certificate
     */
    public function setCertificate(X509Certificate $certificate) {
        $this->certificate = $certificate;
    }

    /**
     * @return X509Certificate
     */
    public function getCertificate() {
        return $this->certificate;
    }


    /**
     * @param \DOMNode $parent
     * @param \AerialShip\LightSaml\Meta\SerializationContext $context
     * @return \DOMNode
     */
    function getXml(\DOMNode $parent, SerializationContext $context)
    {
        $result = $context->getDocument()->createElementNS(Protocol::NS_METADATA, 'md:KeyDescriptor');
        $parent->appendChild($result);
        if ($this->getUse()) {
            $result->setAttribute('use', $this->getUse());
        }
        $keyInfo = $parent->ownerDocument->createElementNS(Protocol::NS_XMLDSIG, 'ds:KeyInfo');
        $result->appendChild($keyInfo);
        $xData = $parent->ownerDocument->createElementNS(Protocol::NS_XMLDSIG, 'ds:X509Data');
        $keyInfo->appendChild($xData);
        $xCert = $parent->ownerDocument->createElementNS(Protocol::NS_XMLDSIG, 'ds:X509Certificate');
        $xData->appendChild($xCert);
        $xCert->nodeValue = $this->getCertificate()->getData();
        return $result;
    }

    /**
     * @param \DOMElement $xml
     * @throws \AerialShip\LightSaml\Error\InvalidXmlException
     */
    public function loadFromXml(\DOMElement $xml)
    {
        if ($xml->localName != 'KeyDescriptor' || $xml->namespaceURI != Protocol::NS_METADATA) {
            throw new InvalidXmlException('Expected KeyDescriptor element and '.Protocol::NS_METADATA.' namespace but got '.$xml->localName);
        }

        $this->setUse($xml->getAttribute('use'));

        $xpath = new \DOMXPath($xml instanceof \DOMDocument ? $xml : $xml->ownerDocument);
        $xpath->registerNamespace('ds', \XMLSecurityDSig::XMLDSIGNS);

        $list = $xpath->query('./ds:KeyInfo/ds:X509Data/ds:X509Certificate', $xml);
        if ($list->length != 1) {
            throw new InvalidXmlException("Missing X509Certificate node");
        }

        /** @var $x509CertificateNode \DOMElement */
        $x509CertificateNode = $list->item(0);
        $certificateData = trim($x509CertificateNode->nodeValue);
        if (!$certificateData) {
            throw new InvalidXmlException("Missing certificate data");
        }

        $this->certificate = new X509Certificate();
        $this->certificate->setData($certificateData);
    }

}