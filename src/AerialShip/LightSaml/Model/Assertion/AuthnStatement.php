<?php

namespace AerialShip\LightSaml\Model\Assertion;

use AerialShip\LightSaml\Error\InvalidXmlException;
use AerialShip\LightSaml\Helper;
use AerialShip\LightSaml\Meta\GetXmlInterface;
use AerialShip\LightSaml\Meta\LoadFromXmlInterface;
use AerialShip\LightSaml\Meta\SerializationContext;
use AerialShip\LightSaml\Meta\XmlRequiredAttributesTrait;
use AerialShip\LightSaml\Protocol;


class AuthnStatement implements GetXmlInterface, LoadFromXmlInterface
{
    use XmlRequiredAttributesTrait;


    /** @var int */
    protected $authnInstant;

    /** @var string */
    protected $sessionIndex;

    /** @var string */
    protected $authnContext;




    /**
     * @param string $authnContext
     */
    public function setAuthnContext($authnContext) {
        $this->authnContext = trim($authnContext);
    }

    /**
     * @return string
     */
    public function getAuthnContext() {
        return $this->authnContext;
    }

    /**
     * @param int|string $authnInstant
     * @throws \InvalidArgumentException
     */
    public function setAuthnInstant($authnInstant) {
        if (is_string($authnInstant)) {
            $authnInstant = Helper::parseSAMLTime($authnInstant);
        } else if (!is_int($authnInstant) || $authnInstant < 1) {
            throw new \InvalidArgumentException('Invalid AuthnInstant');
        }
        $this->authnInstant = $authnInstant;
    }

    /**
     * @return int
     */
    public function getAuthnInstant() {
        return $this->authnInstant;
    }

    /**
     * @param string $sessionIndex
     */
    public function setSessionIndex($sessionIndex) {
        $this->sessionIndex = $sessionIndex;
    }

    /**
     * @return string
     */
    public function getSessionIndex() {
        return $this->sessionIndex;
    }



    protected function prepareForXml() {
        if (!$this->getAuthnInstant()) {
            $this->setAuthnInstant(time());
        }
    }


    /**
     * @param \DOMNode $parent
     * @param \AerialShip\LightSaml\Meta\SerializationContext $context
     * @return \DOMElement
     */
    function getXml(\DOMNode $parent, SerializationContext $context) {
        $result = $context->getDocument()->createElementNS(Protocol::NS_ASSERTION, 'saml:AuthnStatement');
        $parent->appendChild($result);

        $result->setAttribute('AuthnInstant', Helper::time2string($this->getAuthnInstant()));
        if ($this->getSessionIndex()) {
            $result->setAttribute('SessionIndex', $this->getSessionIndex());
        }

        $authnContextNode = $context->getDocument()->createElementNS(Protocol::NS_ASSERTION, 'saml:AuthnContext');
        $result->appendChild($authnContextNode);
        $refNode = $context->getDocument()->createElementNS(Protocol::NS_ASSERTION, 'saml:AuthnContextClassRef', $this->getAuthnContext());
        $authnContextNode->appendChild($refNode);

        return $result;
    }

    /**
     * @param \DOMElement $xml
     * @throws \AerialShip\LightSaml\Error\InvalidXmlException
     */
    function loadFromXml(\DOMElement $xml) {
        if ($xml->localName != 'AuthnStatement' || $xml->namespaceURI != Protocol::NS_ASSERTION) {
            throw new InvalidXmlException('Expected AuthnStatement element but got '.$xml->localName);
        }

        $this->checkRequiredAttributes($xml, array('AuthnInstant'));
        $this->setAuthnInstant($xml->getAttribute('AuthnInstant'));

        if ($xml->hasAttribute('SessionIndex')) {
            $this->setSessionIndex($xml->getAttribute('SessionIndex'));
        }

        $xpath = new \DOMXPath($xml->ownerDocument);
        $xpath->registerNamespace('saml', Protocol::NS_ASSERTION);
        $xpath->registerNamespace('samlp', Protocol::SAML2);

        $list = $xpath->query('./saml:AuthnContext/saml:AuthnContextClassRef', $xml);
        if ($list->length) {
            $this->setAuthnContext($list->item(0)->textContent);
        }
    }


}
