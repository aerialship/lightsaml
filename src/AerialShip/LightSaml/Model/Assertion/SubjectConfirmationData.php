<?php

namespace AerialShip\LightSaml\Model\Assertion;

use AerialShip\LightSaml\Error\InvalidXmlException;
use AerialShip\LightSaml\Helper;
use AerialShip\LightSaml\Meta\GetXmlInterface;
use AerialShip\LightSaml\Meta\LoadFromXmlInterface;
use AerialShip\LightSaml\Meta\SerializationContext;
use AerialShip\LightSaml\Protocol;


class SubjectConfirmationData implements GetXmlInterface, LoadFromXmlInterface
{
    /** @var int */
    protected $notBefore;

    /** @var int */
    protected $notOnOrAfter;

    /** @var string */
    protected $recipient;

    /** @var string */
    protected $inResponseTo;

    /** @var string */
    protected $address;





    /**
     * @param string $address
     */
    public function setAddress($address) {
        $this->address = $address;
    }

    /**
     * @return string
     */
    public function getAddress() {
        return $this->address;
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
     * @param int|string $notBefore
     * @throws \InvalidArgumentException
     */
    public function setNotBefore($notBefore) {
        if (is_string($notBefore)) {
            $notBefore = Helper::parseSAMLTime($notBefore);
        }
        if (!is_int($notBefore) || $notBefore < 1) {
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
        }
        if (!is_int($notOnOrAfter) || $notOnOrAfter < 1) {
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
     * @param string $recipient
     */
    public function setRecipient($recipient) {
        $this->recipient = $recipient;
    }

    /**
     * @return string
     */
    public function getRecipient() {
        return $this->recipient;
    }


    /**
     * @param \DOMNode $parent
     * @param \AerialShip\LightSaml\Meta\SerializationContext $context
     * @return \DOMElement
     */
    function getXml(\DOMNode $parent, SerializationContext $context) {
        $result = $context->getDocument()->createElementNS(Protocol::NS_ASSERTION, 'saml:SubjectConfirmationData');
        $parent->appendChild($result);

        if ($this->getNotBefore()) {
            $result->setAttribute('NotBefore', Helper::time2string($this->getNotBefore()));
        }
        if ($this->getNotOnOrAfter()) {
            $result->setAttribute('NotOnOrAfter', Helper::time2string($this->getNotOnOrAfter()));
        }

        foreach (array('Recipient', 'InResponseTo', 'Address') as $name) {
            $method = "get{$name}";
            if ($this->$method()) {
                $result->setAttribute($name, $this->$method());
            }
        }

        return $result;
    }

    /**
     * @param \DOMElement $xml
     * @throws \AerialShip\LightSaml\Error\InvalidXmlException
     */
    function loadFromXml(\DOMElement $xml) {
        if ($xml->localName != 'SubjectConfirmationData' || $xml->namespaceURI != Protocol::NS_ASSERTION) {
            throw new InvalidXmlException('Expected SubjectConfirmationData element but got '.$xml->localName);
        }

        foreach (array('NotBefore', 'NotOnOrAfter', 'Recipient', 'InResponseTo', 'Address') as $name) {
            if ($xml->hasAttribute($name)) {
                $method = "set{$name}";
                $this->$method($xml->getAttribute($name));
            }
        }
    }


}
