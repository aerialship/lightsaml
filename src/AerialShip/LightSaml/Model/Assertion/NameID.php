<?php

namespace AerialShip\LightSaml\Model\Assertion;

use AerialShip\LightSaml\Error\InvalidXmlException;
use AerialShip\LightSaml\Meta\GetXmlInterface;
use AerialShip\LightSaml\Meta\LoadFromXmlInterface;
use AerialShip\LightSaml\Meta\SerializationContext;
use AerialShip\LightSaml\Protocol;


class NameID implements GetXmlInterface, LoadFromXmlInterface
{
    /** @var string */
    protected $value;

    /** @var string */
    protected $nameQualifier = null;

    /** @var string */
    protected $sPNameQualifier = null;

    /** @var string */
    protected $format = null;

    /** @var string */
    protected $sPProvidedID = null;
    /**
     * @param string $value
     */
    public function setValue($value) {
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getValue() {
        return $this->value;
    }

    /**
     * @param string $nameQualifier
     * @return $this
     */
    public function setNameQualifier($nameQualifier){
        $this->nameQualifier = $nameQualifier;
        return $this;
    }

    /**
     * @return string
     */
    public function getNameQualifier(){
        return $this->nameQualifier;
    }

    /**
     * @param string $sPNameQualifier
     * @return $this
     */
    public function setSPNameQualifier($sPNameQualifier){
        $this->sPNameQualifier = $sPNameQualifier;
        return $this;
    }

    /**
     * @return string
     */
    public function getSPNameQualifier(){
        return $this->sPNameQualifier;
    }

    /**
     * @param string $format
     * @return $this
     */
    public function setFormat($format){
        $this->format = trim($format);
        return $this;
    }

    /**
     * @return string
     */
    public function getFormat(){
        return $this->format;
    }

    /**
     * @param string $sPProvidedID
     * @return $this
     */
    public function setSPProvidedID($sPProvidedID){
        $this->sPProvidedID = $sPProvidedID;
        return $this;
    }

    /**
     * @return string
     */
    public function getSPProvidedID(){
        return $this->sPProvidedID;
    }

    /**
     * @param \DOMNode $parent
     * @param \AerialShip\LightSaml\Meta\SerializationContext $context
     * @return \DOMElement
     */
    function getXml(\DOMNode $parent, SerializationContext $context) {
        $result = $context->getDocument()->createElementNS(Protocol::NS_ASSERTION, 'saml:NameID', $this->getValue());

        $parent->appendChild($result);

        if($this->getSPNameQualifier()) $result->setAttribute('SPNameQualifier', $this->getSPNameQualifier());
        if($this->getNameQualifier())   $result->setAttribute('NameQualifier',   $this->getNameQualifier());
        if($this->getSPProvidedID())    $result->setAttribute('SPProvidedID',    $this->getSPProvidedID());
        if($this->getFormat())          $result->setAttribute('Format',          $this->getFormat());

        return $result;
    }


    /**
     * @param \DOMElement $xml
     * @throws \AerialShip\LightSaml\Error\InvalidXmlException
     */
    function loadFromXml(\DOMElement $xml) {
        if ($xml->localName != 'NameID') {
            throw new InvalidXmlException('Expected NameID element got '.$xml->localName);
        }

        if ($xml->hasAttribute('SPNameQualifier')) $this->setSPNameQualifier($xml->getAttribute('SPNameQualifier'));
        if ($xml->hasAttribute('NameQualifier'))   $this->setNameQualifier($xml->getAttribute('NameQualifier'));
        if ($xml->hasAttribute('SPProvidedID'))    $this->setSPProvidedID($xml->getAttribute('SPProvidedID'));
        if ($xml->hasAttribute('Format'))          $this->setFormat($xml->getAttribute('Format'));
        $this->setValue(trim($xml->textContent));
    }

}