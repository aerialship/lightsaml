<?php

namespace AerialShip\LightSaml\Model\Metadata;

use AerialShip\LightSaml\Error\InvalidXmlException;
use AerialShip\LightSaml\Meta\GetXmlInterface;
use AerialShip\LightSaml\Meta\LoadFromXmlInterface;
use AerialShip\LightSaml\Protocol;
use AerialShip\LightSaml\Security\X509Certificate;


class KeyDescriptor implements GetXmlInterface, LoadFromXmlInterface
{
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
     */
    public function setUse($use) {
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
     * @return \DOMNode
     */
    function getXml(\DOMNode $parent) {
        $result = $parent->ownerDocument->createElementNS(Protocol::NS_METADATA, 'md:KeyDescriptor');
        $parent->appendChild($result);
        $result->setAttribute('use', $this->getUse());
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
    function loadFromXml(\DOMElement $xml) {
        if ($xml->localName != 'KeyDescriptor' || $xml->namespaceURI != Protocol::NS_METADATA) {
            throw new InvalidXmlException('Expected KeyDescriptor element and '.Protocol::NS_METADATA.' namespace but got '.$xml->localName);
        }
        if (!$xml->hasAttribute('use')) {
            throw new InvalidXmlException("Missing use attribute");
        }
        $this->setUse($xml->getAttribute('use'));

        $list = $xml->getElementsByTagName('KeyInfo');
        if ($list->length != 1) {
            throw new InvalidXmlException("Missing KeyInfo node");
        }
        /** @var $keyInfoNode \DOMElement */
        $keyInfoNode = $list->item(0);
        if ($keyInfoNode->namespaceURI != Protocol::NS_XMLDSIG) {
            throw new InvalidXmlException("Invalid namespace of KeyInfo node");
        }

        $list = $keyInfoNode->getElementsByTagName('X509Data');
        if ($list->length != 1) {
            throw new InvalidXmlException("Missing X509Data node");
        }
        /** @var $x509DataNode \DOMElement */
        $x509DataNode = $list->item(0);

        $list = $x509DataNode->getElementsByTagName('X509Certificate');
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


    /**
     * @param \DOMElement $root
     * @return \DOMElement[]  Array of unknown elements that are not required
     * @throws \AerialShip\LightSaml\Error\InvalidXmlException
     */
    public function loadXml(\DOMElement $root) {
        if (!$root->hasAttribute('use')) {
            throw new InvalidXmlException("Missing use attribute");
        }
        $this->use = $root->getAttribute('use');

        $list = $root->getElementsByTagName('KeyInfo');
        if ($list->length != 1) {
            throw new InvalidXmlException("Missing KeyInfo node");
        }
        /** @var $keyInfoNode \DOMElement */
        $keyInfoNode = $list->item(0);
        if ($keyInfoNode->namespaceURI != Protocol::NS_XMLDSIG) {
            throw new InvalidXmlException("Invalid namespace of KeyInfo node");
        }

        $list = $keyInfoNode->getElementsByTagName('X509Data');
        if ($list->length != 1) {
            throw new InvalidXmlException("Missing X509Data node");
        }
        /** @var $x509DataNode \DOMElement */
        $x509DataNode = $list->item(0);

        $list = $x509DataNode->getElementsByTagName('X509Certificate');
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
        return array();
    }

}