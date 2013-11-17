<?php

namespace AerialShip\LightSaml\Model\Protocol;

use AerialShip\LightSaml\Error\InvalidMessageException;
use AerialShip\LightSaml\Error\InvalidXmlException;
use AerialShip\LightSaml\Helper;
use AerialShip\LightSaml\Meta\GetSignedXmlInterface;
use AerialShip\LightSaml\Meta\GetXmlInterface;
use AerialShip\LightSaml\Meta\LoadFromXmlInterface;
use AerialShip\LightSaml\Meta\SerializationContext;
use AerialShip\LightSaml\Meta\XmlChildrenLoaderTrait;
use AerialShip\LightSaml\Meta\XmlRequiredAttributesTrait;
use AerialShip\LightSaml\Model\XmlDSig\Signature;
use AerialShip\LightSaml\Model\XmlDSig\SignatureCreator;
use AerialShip\LightSaml\Protocol;


abstract class Message implements GetXmlInterface, GetSignedXmlInterface, LoadFromXmlInterface
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


    /** @var string */
    protected $relayState;





    public static function fromXML(\DOMElement $xml) {
        if ($xml->namespaceURI !== Protocol::SAML2) {
            throw new InvalidXmlException("Invalid namespace {$xml->namespaceURI}");
        }

        $result = null;
        switch ($xml->localName) {
            case 'AttributeQuery':
                throw new \Exception('Not implemented');
            case 'AuthnRequest':
                $result = new AuthnRequest();
                break;
            case 'LogoutResponse':
                $result = new LogoutResponse();
                break;
            case 'LogoutRequest':
                $result = new LogoutRequest();
                break;
            case 'Response':
                $result = new Response();
                break;
            case 'ArtifactResponse':
                throw new \Exception('Not implemented');
            case 'ArtifactResolve':
                throw new \Exception('Not implemented');
            default:
                throw new InvalidXmlException("Unknown SAML message $xml->localName");
        }

        $result->loadFromXml($xml);
        return $result;
    }



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
     * @param \AerialShip\LightSaml\Model\XmlDSig\Signature $signature
     */
    public function setSignature($signature) {
        $this->signature = $signature;
    }

    /**
     * @return \AerialShip\LightSaml\Model\XmlDSig\Signature
     */
    public function getSignature() {
        return $this->signature;
    }


    /**
     * @param string $relayState
     */
    public function setRelayState($relayState) {
        $this->relayState = $relayState;
    }

    /**
     * @return string
     */
    public function getRelayState() {
        return $this->relayState;
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
     * @param \AerialShip\LightSaml\Meta\SerializationContext $context
     * @return \DOMElement
     */
    function getXml(\DOMNode $parent, SerializationContext $context) {
        $this->prepareForXml();

        if ($this->getXmlNodeNamespace()) {
            $result = $context->getDocument()->createElementNS($this->getXmlNodeNamespace(), $this->getXmlNodeLocalName());
        } else {
            $result = $context->getDocument()->createElement($this->getXmlNodeLocalName());
        }
        $parent->appendChild($result);

        $result->setAttribute('ID', $this->getID());
        $result->setAttribute('Version', $this->getVersion());
        $result->setAttribute('IssueInstant', gmdate('Y-m-d\TH:i:s\Z', $this->getIssueInstant()));
        $result->setAttribute('Destination', $this->getDestination());

        $issuerNode = $context->getDocument()->createElementNS(Protocol::NS_ASSERTION, 'saml:Issuer', $this->getIssuer());
        $result->appendChild($issuerNode);

        return $result;
    }

    /**
     * @param \DOMNode $parent
     * @param SerializationContext $context
     * @throws \AerialShip\LightSaml\Error\InvalidMessageException
     * @return \DOMElement
     */
    function getSignedXml(\DOMNode $parent, SerializationContext $context) {
        $result = $this->getXml($parent, $context);

        if ($signature = $this->getSignature()) {
            if (!$signature instanceof SignatureCreator) {
                throw new InvalidMessageException('Signature must be SignatureCreator');
            }
            $signature->getXml($result, $context);
        }

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

        $this->checkRequiredAttributes($xml, array('ID', 'Version', 'IssueInstant'));
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