<?php

namespace AerialShip\LightSaml\Model\Assertion;

use AerialShip\LightSaml\Error\InvalidAssertionException;
use AerialShip\LightSaml\Error\InvalidXmlException;
use AerialShip\LightSaml\Helper;
use AerialShip\LightSaml\Meta\GetXmlInterface;
use AerialShip\LightSaml\Meta\LoadFromXmlInterface;
use AerialShip\LightSaml\Meta\SerializationContext;
use AerialShip\LightSaml\Meta\XmlRequiredAttributesTrait;
use AerialShip\LightSaml\Model\XmlDSig\Signature;
use AerialShip\LightSaml\Model\XmlDSig\SignatureCreator;
use AerialShip\LightSaml\Model\XmlDSig\SignatureXmlValidator;
use AerialShip\LightSaml\Protocol;


class Assertion implements GetXmlInterface, LoadFromXmlInterface
{
    use XmlRequiredAttributesTrait;


    /** @var string */
    protected $id;

    /** @var int */
    protected $issueInstant;

    /** @var string */
    protected $version = Protocol::VERSION_2_0;

    /** @var string */
    protected $issuer;

    /** @var Signature|null */
    protected $signature;

    /** @var Subject */
    protected $subject;

    /** @var int */
    protected $notBefore;

    /** @var int */
    protected $notOnOrAfter;

    /** @var string[] */
    protected $validAudience;

    /** @var Attribute[] */
    protected $attributes = array();

    /** @var AuthnStatement */
    protected $authnStatement;





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
     * @param string $name
     * @return Attribute|null
     */
    public function getAttribute($name) {
        return @$this->attributes[$name];
    }

    /**
     * @param Attribute $attribute
     * @return $this
     */
    public function addAttribute(Attribute $attribute) {
        $this->attributes[$attribute->getName()] = $attribute;
        return $this;
    }

    /**
     * @return Attribute[]
     */
    public function getAllAttributes() {
        return $this->attributes;
    }

    /**
     * @param $issueInstant
     * @throws \InvalidArgumentException
     * @param int|string $issueInstant
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
     * @param Subject $subject
     */
    public function setSubject($subject) {
        $this->subject = $subject;
    }

    /**
     * @return Subject
     */
    public function getSubject() {
        return $this->subject;
    }

    /**
     * @param int|string $notBefore
     * @throws \InvalidArgumentException
     */
    public function setNotBefore($notBefore) {
        if (is_string($notBefore)) {
            $notBefore = Helper::parseSAMLTime($notBefore);
        } else if (!is_int($notBefore) || $notBefore < 1) {
            throw new \InvalidArgumentException();
        }
        $this->notBefore = $notBefore;
    }

    /**
     * @return int
     */
    public function getNotBefore() {
        return $this->notBefore;
    }

    /**
     * @param int|string $notOnOrAfter
     * @throws \InvalidArgumentException
     */
    public function setNotOnOrAfter($notOnOrAfter) {
        if (is_string($notOnOrAfter)) {
            $notOnOrAfter = Helper::parseSAMLTime($notOnOrAfter);
        } else if (!is_int($notOnOrAfter) || $notOnOrAfter < 1) {
            throw new \InvalidArgumentException();
        }
        $this->notOnOrAfter = $notOnOrAfter;
    }

    /**
     * @return int
     */
    public function getNotOnOrAfter() {
        return $this->notOnOrAfter;
    }

    /**
     * @param Signature|null $signature
     */
    public function setSignature($signature) {
        $this->signature = $signature;
    }

    /**
     * @return Signature|null
     */
    public function getSignature() {
        return $this->signature;
    }

    /**
     * @param string[] $validAudience
     */
    public function setValidAudience(array $validAudience) {
        $this->validAudience = $validAudience;
    }

    /**
     * @return \string[]
     */
    public function getValidAudience() {
        return $this->validAudience;
    }

    /**
     * @param string $value
     */
    public function addValidAudience($value) {
        $this->validAudience[] = $value;
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
     * @param AuthnStatement $authnStatement
     */
    public function setAuthnStatement(AuthnStatement $authnStatement) {
        $this->authnStatement = $authnStatement;
    }

    /**
     * @return AuthnStatement
     */
    public function getAuthnStatement() {
        return $this->authnStatement;
    }




    protected function prepareForXml() {
        if (!$this->getID()) {
            $this->setId(Helper::generateID());
        }
        if (!$this->getIssueInstant()) {
            $this->setIssueInstant(time());
        }
        if (!$this->getIssuer()) {
            throw new InvalidAssertionException('Issuer not set in Assertion');
        }
        if (!$this->getSubject()) {
            throw new InvalidAssertionException('Subject not set in Assertion');
        }
        if (!$this->getNotBefore()) {
            $this->setNotBefore(time());
        }
        if (!$this->getNotOnOrAfter()) {
            $this->setNotOnOrAfter(time());
        }
        if (!$this->getAuthnStatement()) {
            $this->setAuthnStatement(new AuthnStatement());
        }
    }


    /**
     * @param \DOMNode $parent
     * @param \AerialShip\LightSaml\Meta\SerializationContext $context
     * @throws \AerialShip\LightSaml\Error\InvalidAssertionException
     * @return \DOMElement
     */
    function getXml(\DOMNode $parent, SerializationContext $context) {
        $this->prepareForXml();

        $result = $context->getDocument()->createElementNS(Protocol::NS_ASSERTION, 'saml:Assertion');
        $parent->appendChild($result);

        $result->setAttribute('ID', $this->getID());
        $result->setAttribute('Version', $this->getVersion());
        $result->setAttribute('IssueInstant', Helper::time2string($this->getIssueInstant()));

        $issuerNode = $context->getDocument()->createElementNS(Protocol::NS_ASSERTION, 'saml:Issuer', $this->getIssuer());
        $result->appendChild($issuerNode);

        $this->getSubject()->getXml($result, $context);

        $conditionsNode = $context->getDocument()->createElementNS(Protocol::NS_ASSERTION, 'saml:Conditions');
        $result->appendChild($conditionsNode);
        $conditionsNode->setAttribute('NotBefore', Helper::time2string($this->getNotBefore()));
        $conditionsNode->setAttribute('NotOnOrAfter', Helper::time2string($this->getNotOnOrAfter()));
        if ($this->getValidAudience()) {
            $audienceRestrictionNode = $context->getDocument()->createElementNS(Protocol::NS_ASSERTION, 'AudienceRestriction');
            $conditionsNode->appendChild($audienceRestrictionNode);
            foreach ($this->getValidAudience() as $v) {
                $audienceNode = $context->getDocument()->createElementNS(Protocol::NS_ASSERTION, 'Audience', $v);
                $audienceRestrictionNode->appendChild($audienceNode);
            }
        }

        $attributeStatementNode = $context->getDocument()->createElementNS(Protocol::NS_ASSERTION, 'saml:AttributeStatement');
        $result->appendChild($attributeStatementNode);
        foreach ($this->getAllAttributes() as $attribute) {
            $attribute->getXml($attributeStatementNode, $context);
        }

        $this->getAuthnStatement()->getXml($result, $context);

        if ($signature = $this->getSignature()) {
            if (!$signature instanceof SignatureCreator) {
                throw new InvalidAssertionException('Signature must be SignatureCreator');
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
        if ($xml->localName != 'Assertion' || $xml->namespaceURI != Protocol::NS_ASSERTION) {
            throw new InvalidXmlException('Expected Assertion element but got '.$xml->localName);
        }

        $this->checkRequiredAttributes($xml, array('ID', 'Version', 'IssueInstant'));
        $this->setID($xml->getAttribute('ID'));
        $this->setVersion($xml->getAttribute('Version'));
        $this->setIssueInstant($xml->getAttribute('IssueInstant'));

        $xpath = new \DOMXPath($xml instanceof \DOMDocument ? $xml : $xml->ownerDocument);
        $xpath->registerNamespace('saml', Protocol::NS_ASSERTION);

        $signatureNode = null;
        /** @var $node \DOMElement */
        for ($node = $xml->firstChild; $node !== NULL; $node = $node->nextSibling) {
            if ($node->localName == 'Issuer') {
                $this->setIssuer(trim($node->textContent));
            } else if ($node->localName == 'Subject') {
                $this->setSubject(new Subject());
                $this->getSubject()->loadFromXml($node);
            } else if ($node->localName == 'Conditions') {
                $this->loadXmlConditions($node, $xpath);
            } else if ($node->localName == 'AttributeStatement') {
                $this->loadXmlAttributeStatement($xml, $xpath);
            } else if ($node->localName == 'AuthnStatement') {
                $this->setAuthnStatement(new AuthnStatement());
                $this->getAuthnStatement()->loadFromXml($node);
            } else if ($node->localName == 'Signature' && $node->namespaceURI == Protocol::NS_XMLDSIG) {
                $signatureNode = $node;
            }
        }

        if ($signatureNode) {
            $signature = new SignatureXmlValidator();
            $signature->loadFromXml($signatureNode);
            $this->setSignature($signature);
        }
    }


    private function loadXmlConditions(\DOMElement $node, \DOMXPath $xpath)
    {
        if ($node->hasAttribute('NotBefore')) {
            $this->setNotBefore($node->getAttribute('NotBefore'));
        }
        if ($node->hasAttribute('NotOnOrAfter')) {
            $this->setNotOnOrAfter($node->getAttribute('NotOnOrAfter'));
        }
        /** @var $list \DOMElement[] */
        $list = $xpath->query('./saml:AudienceRestriction/saml:Audience', $node);
        foreach ($list as $a) {
            $this->addValidAudience($a->textContent);
        }
    }


    private function loadXmlAttributeStatement(\DOMElement $root, \DOMXPath $xpath)
    {
        /** @var $list \DOMElement[] */
        $list = $xpath->query('./saml:AttributeStatement/saml:Attribute', $root);
        foreach ($list as $a) {
            $attr = new Attribute();
            $attr->loadFromXml($a);
            $this->addAttribute($attr);
        }
    }

}