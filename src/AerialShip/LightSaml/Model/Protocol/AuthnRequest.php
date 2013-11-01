<?php

namespace AerialShip\LightSaml\Model\Protocol;

use AerialShip\LightSaml\Error\InvalidRequestException;
use AerialShip\LightSaml\Error\InvalidXmlException;
use AerialShip\LightSaml\Helper;
use AerialShip\LightSaml\Meta\GetXmlInterface;
use AerialShip\LightSaml\Meta\LoadFromXmlInterface;
use AerialShip\LightSaml\Meta\XmlRequiredAttributesTrait;
use AerialShip\LightSaml\Model\Metadata\EntityDescriptor;
use AerialShip\LightSaml\NameIDPolicy;
use AerialShip\LightSaml\Protocol;


class AuthnRequest implements GetXmlInterface, LoadFromXmlInterface
{
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
    protected $assertionConsumerServiceURL;

    /** @var string */
    protected $protocolBinding;

    /** @var string */
    protected $issuer;

    /** @var string */
    protected $nameIdPolicyFormat = NameIDPolicy::PERSISTENT;

    /** @var bool */
    protected $nameIdPolicyAllowCreate = true;


    /**
     * @param string $assertionConsumerServiceURL
     */
    public function setAssertionConsumerServiceURL($assertionConsumerServiceURL) {
        $this->assertionConsumerServiceURL = $assertionConsumerServiceURL;
    }

    /**
     * @return string
     */
    public function getAssertionConsumerServiceURL() {
        return $this->assertionConsumerServiceURL;
    }

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
     * @throws \InvalidArgumentException
     */
    public function setId($id) {
        $this->id = trim($id);
        if (!$this->id) {
            throw new \InvalidArgumentException('AuthnRequest ID field can not be empty');
        }
    }

    /**
     * @return string
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param int $issueInstant
     * @throws \InvalidArgumentException
     */
    public function setIssueInstant($issueInstant) {
        if (!is_int($issueInstant)) {
            throw new \InvalidArgumentException('issueInstant must be int');
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
     * @param int $issuer
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
     * @param bool $nameIdPolicyAllowCreate
     */
    public function setNameIdPolicyAllowCreate($nameIdPolicyAllowCreate) {
        $this->nameIdPolicyAllowCreate = (bool)$nameIdPolicyAllowCreate;
    }

    /**
     * @return bool
     */
    public function getNameIdPolicyAllowCreate() {
        return $this->nameIdPolicyAllowCreate;
    }

    /**
     * @param string $nameIdPolicyFormat
     * @throws \InvalidArgumentException
     */
    public function setNameIdPolicyFormat($nameIdPolicyFormat) {
        if (!NameIDPolicy::isValid($nameIdPolicyFormat)) {
            throw new \InvalidArgumentException('Invalid NameIDPolicy');
        }
        $this->nameIdPolicyFormat = $nameIdPolicyFormat;
    }

    /**
     * @return string
     */
    public function getNameIdPolicyFormat() {
        return $this->nameIdPolicyFormat;
    }

    /**
     * @param string $protocolBinding
     */
    public function setProtocolBinding($protocolBinding) {
        $this->protocolBinding = $protocolBinding;
    }

    /**
     * @return string
     */
    public function getProtocolBinding() {
        return $this->protocolBinding;
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
        $id = trim($this->getId());
        if (!$id) {
            throw new InvalidRequestException('AuthnRequest ID not set');
        }
        if (!$this->getIssueInstant()) {
            $this->setIssueInstant(time());
        }
        if (!$this->getDestination()) {
            throw new InvalidRequestException('AuthnRequest Destination not set');
        }
        if (!$this->getAssertionConsumerServiceURL()) {
            throw new InvalidRequestException('AuthRequest AssertionConsumerServiceURL not set');
        }
        if (!$this->getProtocolBinding()) {
            throw new InvalidRequestException('AuthnRequest ProtocolBinding not set');
        }
        if (!$this->getIssuer()) {
            throw new InvalidRequestException('AuthnRequest Issuert not set');
        }
        if (!NameIDPolicy::isValid($this->getNameIdPolicyFormat())) {
            throw new InvalidRequestException('AuthnRequest NameIDPolicy Format not set');
        }
    }


    /**
     * @param \DOMNode $parent
     * @return \DOMElement
     */
    function getXml(\DOMNode $parent) {
        $this->prepareForXml();
        $doc = $parent instanceof \DOMDocument ? $parent : $parent->ownerDocument;
        $result = $doc->createElementNS(Protocol::SAML2, 'samlp:AuthnRequest');
        $parent->appendChild($result);
        $result->setAttribute('ID', $this->getId());
        $result->setAttribute('Version', $this->getVersion());
        $result->setAttribute('IssueInstant', gmdate('Y-m-d\TH:i:s\Z', $this->getIssueInstant()));
        $result->setAttribute('Destination', $this->getDestination());
        $result->setAttribute('AssertionConsumerServiceURL', $this->getAssertionConsumerServiceURL());
        $result->setAttribute('ProtocolBinding', $this->getProtocolBinding());

        $issuerNode = $doc->createElementNS(Protocol::NS_ASSERTION, 'saml:Issuer');
        $result->appendChild($issuerNode);
        $issuerNode->nodeValue = $this->getIssuer();

        $nameIDPolicyNode = $doc->createElementNS(Protocol::SAML2, 'samlp:NameIDPolicy');
        $result->appendChild($nameIDPolicyNode);
        $nameIDPolicyNode->setAttribute('Format', $this->getNameIdPolicyFormat());
        $nameIDPolicyNode->setAttribute('AllowCreate', $this->getNameIdPolicyAllowCreate() ? 'true' : 'false');

        return $result;
    }


    /**
     * @param \DOMElement $xml
     * @throws \AerialShip\LightSaml\Error\InvalidXmlException
     * @return \DOMElement[]
     */
    function loadFromXml(\DOMElement $xml) {
        if ($xml->localName != 'AuthnRequest' || $xml->namespaceURI != Protocol::SAML2) {
            throw new InvalidXmlException('Expected AuthnRequest node in '.Protocol::SAML2.' namespace but got'.$xml->localName);
        }
        $this->checkRequiredAttributes($xml, array('ID', 'Version', 'IssueInstant', 'Destination', 'AssertionConsumerServiceURL', 'ProtocolBinding'));
        $this->setId($xml->getAttribute('ID'));
        $this->setVersion($xml->getAttribute('Version'));
        $this->setIssueInstant(Helper::parseSAMLTime($xml->getAttribute('IssueInstant')));
        $this->setDestination($xml->getAttribute('Destination'));
        $this->setAssertionConsumerServiceURL($xml->getAttribute('AssertionConsumerServiceURL'));
        $this->setProtocolBinding($xml->getAttribute('ProtocolBinding'));

        $result = array();
        for ($node = $xml->firstChild; $node !== NULL; $node = $node->nextSibling) {
            if (!$node instanceof \DOMElement) {
                continue;
            }
            if ($node->localName == 'Issuer' && $node->namespaceURI == Protocol::NS_ASSERTION) {
                $this->setIssuer($node->textContent);
            } else if ($node->localName == 'NameIDPolicy' && $node->namespaceURI == Protocol::SAML2) {
                $this->checkRequiredAttributes($node, array('Format', 'AllowCreate'));
                $this->setNameIdPolicyFormat($node->getAttribute('Format'));
                $this->setNameIdPolicyAllowCreate($node->getAttribute('AllowCreate') == 'true');
            } else {
                $result[] = $node;
            }
        }
        return $result;
    }

}