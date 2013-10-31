<?php

namespace AerialShip\LightSaml\Model;


use AerialShip\LightSaml\Error\InvalidXmlException;
use AerialShip\LightSaml\Protocol;

class Attribute implements GetXmlInterface, LoadFromXmlInterface
{
    /** @var string */
    protected $name;

    /** @var string[] */
    protected $values = array();



    /**
     * @param string $name
     */
    public function setName($name) {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @param string[] $values
     */
    public function setValues(array $values) {
        $this->values = $values;
    }

    /**
     * @return string[]
     */
    public function getValues() {
        return $this->values;
    }


    /**
     * @param string $value
     */
    public function addValue($value) {
        $this->values[] = $value;
    }



    /**
     * @param \DOMNode $parent
     * @return \DOMElement
     */
    function getXml(\DOMNode $parent) {
        $doc = $parent instanceof \DOMDocument ? $parent : $parent->ownerDocument;
        $result = $doc->createElement('Attribute');
        $parent->appendChild($result);

        $result->setAttribute('Name', $this->getName());

        foreach ($this->getValues() as $v) {
            $valueNode = $doc->createElement('AttributeValue', $v);
            $result->appendChild($valueNode);
        }

        return $result;
    }

    /**
     * @param \DOMElement $xml
     * @throws \AerialShip\LightSaml\Error\InvalidXmlException
     * @return \DOMElement[]
     */
    function loadFromXml(\DOMElement $xml) {
        if ($xml->localName != 'Attribute' || $xml->namespaceURI != Protocol::NS_ASSERTION) {
            throw new InvalidXmlException('Expected Attribute element but got '.$xml->localName);
        }

        if (!$xml->hasAttribute('Name')) {
            throw new InvalidXmlException('Missing Attribute Name');
        }
        $this->setName($xml->getAttribute('Name'));

        for ($node = $xml->firstChild; $node !== NULL; $node = $node->nextSibling) {
            if ($node->localName != 'AttributeValue') {
                throw new InvalidXmlException('Expected AttributeValue but got '.$node->localName);
            }
            $this->addValue($node->textContent);
        }

        return array();
    }


}