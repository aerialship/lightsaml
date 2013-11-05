<?php

namespace AerialShip\LightSaml\Model\Protocol;

use AerialShip\LightSaml\Error\InvalidMessageException;
use AerialShip\LightSaml\Error\InvalidXmlException;
use AerialShip\LightSaml\Helper;
use AerialShip\LightSaml\Meta\GetXmlInterface;
use AerialShip\LightSaml\Meta\LoadFromXmlInterface;
use AerialShip\LightSaml\Meta\XmlChildrenLoaderTrait;
use AerialShip\LightSaml\Meta\XmlRequiredAttributesTrait;
use AerialShip\LightSaml\Model\XmlDSig\Signature;
use AerialShip\LightSaml\Protocol;


abstract class Message implements GetXmlInterface, LoadFromXmlInterface
{
    use XmlRequiredAttributesTrait;
    use XmlChildrenLoaderTrait;


    /** @var string */
    protected $id;

    /** @var string */
    protected $version = Protocol::VERSION_2_0;

    /** @var int */
    protected $issueInstant;

    /** @var string */
    protected $destination;

    /** @var string */
    protected $issuer;

    /** @var Signature */
    protected $signature;


    /**
     * @return string
     */
    abstract function getXmlNodeLocalName();

    /**
     * @return string|null
     */
    abstract function getXmlNodeNamespace();



    /**
     * @param string $id
     * @throws \InvalidArgumentException
     */
    public function setID($id) {
        $this->id = trim($id);
        if (!$this->id) {
            throw new \InvalidArgumentException('AuthnRequest ID field can not be empty');
        }
    }

    /**
     * @return string
     */
    public function getID() {
        return $this->id;
    }

    /**
     * @param string $version
     */
    public function setVersion($version) {
        $this->version = trim($version);
    }

    /**
     * @return string
     */
    public function getVersion() {
        return $this->version;
    }

    /**
     * @param int $issueInstant
     * @throws \InvalidArgumentException
     */
    public function setIssueInstant($issueInstant) {
        if (is_string($issueInstant)) {
            $issueInstant = Helper::parseSAMLTime($issueInstant);
        } else if (!is_int($issueInstant) || $issueInstant < 1) {
            throw new \InvalidArgumentException('Invalid IssueInstant');
        }
        $this->issueInstant = $issueInstant;
    }

    /**
     * @return int
     */
    public function getIssueInstant() {
        return $this->issueInstant;
    }

    /**
     * @param string $destination
     */
    public function setDestination($destination) {
        $this->destination = trim($destination);
    }

    /**
     * @return string
     */
    public function getDestination() {
        return $this->destination;
    }


    /**
     * @param int $issuer
     */
    public function setIssuer($issuer) {
        $this->issuer = trim($issuer);
    }

    /**
     * @return string
     */
    public function getIssuer() {
        return $this->issuer;
    }


    /**
     * @throws \AerialShip\LightSaml\Error\InvalidRequestException
     */
    protected function prepareForXml() {
        if (!$this->getID()) {
            throw new InvalidMessageException('ID not set');
        }
        if (!$this->getVersion()) {
            throw new InvalidMessageException('Version not set');
        }
        if (!$this->getIssueInstant()) {
            $this->setIssueInstant(time());
        }
        if (!$this->getDestination()) {
            throw new InvalidMessageException('Destination not set');
        }
        if (!$this->getIssuer()) {
            throw new InvalidMessageException('Issuer not set');
        }
    }


    /**
     * @param \DOMNode $parent
     * @return \DOMElement
     */
    function getXml(\DOMNode $parent) {
        $this->prepareForXml();

        $doc = $parent instanceof \DOMDocument ? $parent : $parent->ownerDocument;
        if ($this->getXmlNodeNamespace()) {
            $result = $doc->createElementNS($this->getXmlNodeNamespace(), $this->getXmlNodeLocalName());
        } else {
            $result = $doc->createElement($this->getXmlNodeLocalName());
        }
        $parent->appendChild($result);

        $result->setAttribute('ID', $this->getID());
        $result->setAttribute('Version', $this->getVersion());
        $result->setAttribute('IssueInstant', gmdate('Y-m-d\TH:i:s\Z', $this->getIssueInstant()));
        $result->setAttribute('Destination', $this->getDestination());

        $issuerNode = $doc->createElementNS(Protocol::NS_ASSERTION, 'saml:Issuer', $this->getIssuer());
        $result->appendChild($issuerNode);

        return $result;
    }


    /**
     * @param \DOMElement $xml
     * @throws \AerialShip\LightSaml\Error\InvalidXmlException
     */
    function loadFromXml(\DOMElement $xml) {
        if ($xml->localName != $this->getXmlNodeLocalName()) {
            throw new InvalidXmlException('Expected '.$this->getXmlNodeLocalName().' node but got '.$xml->localName);
        }
        if ($this->getXmlNodeNamespace() && $xml->namespaceURI != $this->getXmlNodeNamespace()) {
            throw new InvalidXmlException('Expected '.$this->getXmlNodeNamespace().' namespace but got'.$xml->namespaceURI);
        }

        $this->checkRequiredAttributes($xml, array('ID', 'Version', 'IssueInstant', 'Destination'));
        $this->setID($xml->getAttribute('ID'));
        $this->setVersion($xml->getAttribute('Version'));
        $this->setIssueInstant(Helper::parseSAMLTime($xml->getAttribute('IssueInstant')));
        $this->setDestination($xml->getAttribute('Destination'));


        $this->iterateChildrenElements($xml, function(\DOMElement $node) {
            if ($node->localName == 'Issuer' && $node->namespaceURI == Protocol::NS_ASSERTION) {
                $this->setIssuer($node->textContent);
            }
        });
    }

} 