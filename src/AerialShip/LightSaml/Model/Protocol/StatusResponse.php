<?php

namespace AerialShip\LightSaml\Model\Protocol;

use AerialShip\LightSaml\Error\InvalidResponseException;
use AerialShip\LightSaml\Error\InvalidXmlException;
use AerialShip\LightSaml\Helper;
use AerialShip\LightSaml\Meta\GetXmlInterface;
use AerialShip\LightSaml\Meta\LoadFromXmlInterface;
use AerialShip\LightSaml\Meta\XmlChildrenLoaderTrait;
use AerialShip\LightSaml\Meta\XmlRequiredAttributesTrait;
use AerialShip\LightSaml\Protocol;


abstract class StatusResponse implements GetXmlInterface, LoadFromXmlInterface
{
    use XmlChildrenLoaderTrait;
    use XmlRequiredAttributesTrait;


    /** @var string */
    protected $id;

    /** @var string */
    protected $version = Protocol::VERSION_2_0;

    /** @var int */
    protected $issueInstant;

    /** @var string */
    protected $destination;

    /** @var string */
    protected $inResponseTo;


    /** @var string */
    protected $issuer;


    /** @var Status */
    protected $status;



    /**
     * @return string
     */
    abstract function getXmlNodeLocalName();

    /**
     * @return string|null
     */
    abstract function getXmlNodeNamespace();




    /**
     * @param string $destination
     */
    public function setDestination($destination) {
        $this->destination = $destination;
    }

    /**
     * @return string
     */
    public function getDestination() {
        return $this->destination;
    }

    /**
     * @param string $id
     */
    public function setID($id) {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getID() {
        return $this->id;
    }

    /**
     * @param string $inResponseTo
     */
    public function setInResponseTo($inResponseTo) {
        $this->inResponseTo = $inResponseTo;
    }

    /**
     * @return string
     */
    public function getInResponseTo() {
        return $this->inResponseTo;
    }

    /**
     * @param int|string $issueInstant
     * @throws \InvalidArgumentException
     */
    public function setIssueInstant($issueInstant) {
        if (is_string($issueInstant)) {
            $issueInstant = Helper::parseSAMLTime($issueInstant);
        } else if (!is_int($issueInstant) || $issueInstant < 1) {
            throw new \InvalidArgumentException('Invalid IssueInstance');
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
     * @param string $issuer
     */
    public function setIssuer($issuer) {
        $this->issuer = $issuer;
    }

    /**
     * @return string
     */
    public function getIssuer() {
        return $this->issuer;
    }

    /**
     * @param \AerialShip\LightSaml\Model\Protocol\Status $status
     */
    public function setStatus($status) {
        $this->status = $status;
    }

    /**
     * @return \AerialShip\LightSaml\Model\Protocol\Status
     */
    public function getStatus() {
        return $this->status;
    }

    /**
     * @param string $version
     */
    public function setVersion($version) {
        $this->version = $version;
    }

    /**
     * @return string
     */
    public function getVersion() {
        return $this->version;
    }





    protected function prepareForXml() {
        if (!$this->getID()) {
            $this->setID(Helper::generateID());
        }
        if (!$this->getVersion()) {
            $this->setVersion(Protocol::VERSION_2_0);
        }
        if (!$this->getIssueInstant()) {
            $this->setIssueInstant(time());
        }
        if (!$this->getDestination()) {
            throw new InvalidResponseException('Missing Destination');
        }
        if (!$this->getInResponseTo()) {
            throw new InvalidResponseException('Missing InResponseTo');
        }
        if (!$this->getIssuer()) {
            throw new InvalidResponseException('Missing Issuer');
        }
        if (!$this->getStatus()) {
            throw new InvalidResponseException('Missing Status');
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
        $result->setAttribute('InResponseTo', $this->getInResponseTo());

        $issuerNode = $doc->createElementNS(Protocol::NS_ASSERTION, 'Issuer', $this->getIssuer());
        $result->appendChild($issuerNode);

        $this->getStatus()->getXml($result);

        return $result;
    }

    /**
     * @param \DOMElement $xml
     * @throws \AerialShip\LightSaml\Error\InvalidXmlException
     * @return \DOMElement[]
     */
    function loadFromXml(\DOMElement $xml) {
        if ($xml->localName != $this->getXmlNodeLocalName()) {
            throw new InvalidXmlException('Expected '.$this->getXmlNodeLocalName().' node but got '.$xml->localName);
        }
        if ($this->getXmlNodeNamespace() && $xml->namespaceURI != $this->getXmlNodeNamespace()) {
            throw new InvalidXmlException('Expected '.$this->getXmlNodeNamespace().' namespace but got'.$xml->namespaceURI);
        }

        $this->checkRequiredAttributes($xml, array('ID', 'Version', 'IssueInstant', 'Destination', 'InResponseTo'));
        $this->setID($xml->getAttribute('ID'));
        $this->setVersion($xml->getAttribute('Version'));
        $this->setIssueInstant($xml->getAttribute('IssueInstant'));
        $this->setDestination($xml->getAttribute('Destination'));
        $this->setInResponseTo($xml->getAttribute('InResponseTo'));

        $result = array();
        $this->iterateChildrenElements($xml, function(\DOMElement $node) use (&$result) {
            if ($node->localName == 'Issuer' && $node->namespaceURI == Protocol::NS_ASSERTION) {
                $this->setIssuer(trim($node->textContent));
            } else if ($node->localName == 'Status' && $node->namespaceURI == Protocol::SAML2) {
                $this->setStatus(new Status());
                $result = array_merge($result, $this->getStatus()->loadFromXml($node));
            }
        });
        if (!$this->getStatus()) {
            throw new InvalidXmlException('Missing Status element');
        }
        return array();
    }


}