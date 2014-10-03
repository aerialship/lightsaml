<?php

namespace AerialShip\LightSaml\Model\Assertion;

use AerialShip\LightSaml\Error\InvalidXmlException;
use AerialShip\LightSaml\Meta\GetXmlInterface;
use AerialShip\LightSaml\Meta\LoadFromXmlInterface;
use AerialShip\LightSaml\Meta\SerializationContext;
use AerialShip\LightSaml\Protocol;


class Attribute implements GetXmlInterface, LoadFromXmlInterface
{
    /** @var string */
    protected $name;

    /** @var  string|null */
    protected $nameFormat;

    /** @var string */
    protected $friendlyName;

    /** @var string[] */
    protected $values = array();


    /**
     * @param string $name
     * @param string[] $values
     * @param string|null $friendlyName
     */
    public function __construct($name = null, array $values = array(), $friendlyName = null)
    {
        $this->name = $name;
        $this->values = $values;
        $this->friendlyName = $friendlyName;
    }


    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param null|string $nameFormat
     * @return $this|Attribute
     */
    public function setNameFormat($nameFormat)
    {
        $this->nameFormat = $nameFormat;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getNameFormat()
    {
        return $this->nameFormat;
    }

    /**
     * @param string $friendlyName
     */
    public function setFriendlyName($friendlyName)
    {
        $this->friendlyName = $friendlyName;
    }

    /**
     * @return string
     */
    public function getFriendlyName()
    {
        return $this->friendlyName;
    }

    /**
     * @param string[] $values
     */
    public function setValues(array $values)
    {
        $this->values = $values;
    }

    /**
     * @return string[]
     */
    public function getValues()
    {
        return $this->values;
    }


    /**
     * @param string $value
     */
    public function addValue($value)
    {
        $this->values[] = $value;
    }


    public function getFirstValue()
    {
        return $this->values[0];
    }


    /**
     * @param \DOMNode $parent
     * @param \AerialShip\LightSaml\Meta\SerializationContext $context
     * @return \DOMElement
     */
    public function getXml(\DOMNode $parent, SerializationContext $context)
    {
        $result = $context->getDocument()->createElementNS(Protocol::NS_ASSERTION, 'saml:Attribute');
        $parent->appendChild($result);

        $result->setAttribute('Name', $this->getName());
        if ($this->getNameFormat()) {
            $result->setAttribute('NameFormat', $this->getNameFormat());
        }
        if ($this->getFriendlyName()) {
            $result->setAttribute('FriendlyName', $this->getFriendlyName());
        }

        foreach ($this->getValues() as $v) {
            $valueNode = $context->getDocument()->createElementNS(Protocol::NS_ASSERTION, 'saml:AttributeValue', $v);
            $result->appendChild($valueNode);
        }

        return $result;
    }

    /**
     * @param \DOMElement $xml
     * @throws \AerialShip\LightSaml\Error\InvalidXmlException
     */
    public function loadFromXml(\DOMElement $xml)
    {
        if ($xml->localName != 'Attribute' || $xml->namespaceURI != Protocol::NS_ASSERTION) {
            throw new InvalidXmlException('Expected Attribute element but got '.$xml->localName);
        }

        if (!$xml->hasAttribute('Name')) {
            throw new InvalidXmlException('Missing Attribute Name');
        }
        $this->setName($xml->getAttribute('Name'));

        if ($xml->hasAttribute('NameFormat')) {
            $this->setNameFormat($xml->getAttribute('NameFormat'));
        }
        if ($xml->hasAttribute('FriendlyName')) {
            $this->setFriendlyName($xml->getAttribute('FriendlyName'));
        }

        for ($node = $xml->firstChild; $node !== NULL; $node = $node->nextSibling) {
            if ($node->localName != 'AttributeValue') {
                throw new InvalidXmlException('Expected AttributeValue but got '.$node->localName);
            }
            $this->addValue($node->textContent);
        }
    }


}