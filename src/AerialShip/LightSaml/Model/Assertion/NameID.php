<?php

namespace AerialShip\LightSaml\Model\Assertion;

use AerialShip\LightSaml\Error\InvalidNameIDException;
use AerialShip\LightSaml\Error\InvalidXmlException;
use AerialShip\LightSaml\Meta\GetXmlInterface;
use AerialShip\LightSaml\Meta\LoadFromXmlInterface;
use AerialShip\LightSaml\Protocol;


class NameID implements GetXmlInterface, LoadFromXmlInterface
{
    private static $validAttributes = array('NameQualifier'=>1, 'SPNameQualifier'=>1, 'Format'=>1);

    /** @var string */
    protected $value;

    /** @var string[] */
    protected $attributes = array();


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
     * @return string[]
     */
    public function getAttributes() {
        return $this->attributes;
    }

    /**
     * @param string $name
     * @return string|null
     */
    public function getAttribute($name) {
        return @$this->attributes[$name];
    }


    /**
     * @param string $name
     * @param string $value
     * @throws \AerialShip\LightSaml\Error\InvalidNameIDException
     * @return $this
     */
    public function addAttribute($name, $value) {
        if (!isset(self::$validAttributes[$name])) {
            throw new InvalidNameIDException("Invalid NameID attribute $name");
        }
        $this->attributes[$name] = trim($value);
        return $this;
    }




    /**
     * @param \DOMNode $parent
     * @return \DOMElement
     */
    function getXml(\DOMNode $parent) {
        $doc = $parent instanceof \DOMDocument ? $parent : $parent->ownerDocument;
        $result = $doc->createElement('NameID', $this->getValue());
        $parent->appendChild($result);

        foreach ($this->getAttributes() as $k=>$v) {
            if ($v) {
                $result->setAttribute($k, $v);
            }
        }
        return $result;
    }


    /**
     * @param \DOMElement $xml
     * @throws \AerialShip\LightSaml\Error\InvalidXmlException
     */
    function loadFromXml(\DOMElement $xml) {
        if ($xml->localName != 'NameID' || $xml->namespaceURI != Protocol::NS_ASSERTION) {
            throw new InvalidXmlException('Expected NameID element got '.$xml->localName);
        }
        $this->value = trim($xml->textContent);
        foreach (array_keys(self::$validAttributes) as $name) {
            if ($xml->hasAttribute($name)) {
                $this->addAttribute($name, $xml->getAttribute($name));
            }
        }
    }


}