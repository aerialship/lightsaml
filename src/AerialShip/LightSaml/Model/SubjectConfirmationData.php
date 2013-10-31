<?php

namespace AerialShip\LightSaml\Model;


use AerialShip\LightSaml\Error\InvalidXmlException;
use AerialShip\LightSaml\Helper;
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
     * @return \DOMElement
     */
    function getXml(\DOMNode $parent) {
        $doc = $parent instanceof \DOMDocument ? $parent : $parent->ownerDocument;
        $result = $doc->createElement('SubjectConfirmationData');
        $parent->appendChild($result);

        if ($this->getNotBefore()) {
            $result->setAttribute('NotBefore', gmdate('Y-m-d\TH:i:s\Z', $this->getNotBefore()));
        }
        if ($this->getNotOnOrAfter()) {
            $result->setAttribute('NotOnOrAfter', gmdate('Y-m-d\TH:i:s\Z', $this->getNotBefore()));
        }

        foreach (array('Recipient', 'InResponseTo', 'Address') as $name) {
            $method = "set{$name}";
            if ($this->$method()) {
                $result->setAttribute($name, $this->$method());
            }
        }

        return $result;
    }

    /**
     * @param \DOMElement $xml
     * @throws \AerialShip\LightSaml\Error\InvalidXmlException
     * @return \DOMElement[]
     */
    function loadFromXml(\DOMElement $xml) {
        if ($xml->localName != 'SubjectConfirmation' || $xml->namespaceURI != Protocol::NS_ASSERTION) {
            throw new InvalidXmlException('Expected Subject element but got '.$xml->localName);
        }

        foreach (array('NotBefore', 'NotOnOrAfter', 'Recipient', 'InResponseTo', 'Address') as $name) {
            if ($xml->hasAttribute($name)) {
                $method = "set{$name}";
                $this->$method($xml->getAttribute($name));
            }
        }

        return array();
    }


}