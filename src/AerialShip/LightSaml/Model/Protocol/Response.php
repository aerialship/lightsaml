<?php

namespace AerialShip\LightSaml\Model\Protocol;

use AerialShip\LightSaml\Error\InvalidResponseException;
use AerialShip\LightSaml\Error\InvalidXmlException;
use AerialShip\LightSaml\Helper;
use AerialShip\LightSaml\Meta\GetXmlInterface;
use AerialShip\LightSaml\Meta\LoadFromXmlInterface;
use AerialShip\LightSaml\Meta\XmlChildrenLoaderTrait;
use AerialShip\LightSaml\Meta\XmlRequiredAttributesTrait;
use AerialShip\LightSaml\Model\Assertion\Assertion;
use AerialShip\LightSaml\Protocol;

class Response implements GetXmlInterface, LoadFromXmlInterface
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

    /** @var Assertion[] */
    protected $assertions = array();




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


    /**
     * @return \AerialShip\LightSaml\Model\Assertion\Assertion[]
     */
    public function getAllAssertions() {
        return $this->assertions;
    }

    public function addAssertion(Assertion $assertion) {
        $this->assertions[] = $assertion;
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
        if (!$this->getAllAssertions()) {
            throw new InvalidResponseException('Missing Assertions');
        }
    }


    /**
     * @param \DOMNode $parent
     * @return \DOMElement
     */
    function getXml(\DOMNode $parent) {
        $this->prepareForXml();

        $doc = $parent instanceof \DOMDocument ? $parent : $parent->ownerDocument;
        $result = $doc->createElementNS(Protocol::SAML2, 'samlp:Response');
        $parent->appendChild($result);

        $result->setAttribute('ID', $this->getID());
        $result->setAttribute('Version', $this->getVersion());
        $result->setAttribute('IssueInstant', gmdate('Y-m-d\TH:i:s\Z', $this->getIssueInstant()));
        $result->setAttribute('Destination', $this->getDestination());
        $result->setAttribute('InResponseTo', $this->getInResponseTo());

        $issuerNode = $doc->createElementNS(Protocol::NS_ASSERTION, 'Issuer', $this->getIssuer());
        $result->appendChild($issuerNode);

        $this->getStatus()->getXml($result);

        foreach ($this->getAllAssertions() as $assertion) {
            $assertion->getXml($result);
        }

        return $result;
    }

    /**
     * @param \DOMElement $xml
     * @throws \AerialShip\LightSaml\Error\InvalidXmlException
     * @return \DOMElement[]
     */
    function loadFromXml(\DOMElement $xml) {
        if ($xml->localName != 'Response' || $xml->namespaceURI != Protocol::SAML2) {
            throw new InvalidXmlException('Expected Response element but got '.$xml->localName);
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
            } else if ($node->localName == 'Assertion' && $node->namespaceURI == Protocol::NS_ASSERTION) {
                $assertion = new Assertion();
                $result = array_merge($result, $assertion->loadFromXml($node));
                $this->addAssertion($assertion);
            } else {
                $result[] = $node;
            }
        });
        if (!$this->getStatus()) {
            throw new InvalidXmlException('Missing Status element');
        }
        if (!$this->getAllAssertions()) {
            throw new InvalidXmlException('Missing Assertion element');
        }
        return $result;
    }


}