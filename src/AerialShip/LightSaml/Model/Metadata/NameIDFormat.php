<?php

namespace AerialShip\LightSaml\Model\Metadata;

use AerialShip\LightSaml\Error\InvalidXmlException;
use AerialShip\LightSaml\Meta\GetXmlInterface;
use AerialShip\LightSaml\Meta\LoadFromXmlInterface;
use AerialShip\LightSaml\Meta\SerializationContext;
use AerialShip\LightSaml\NameIDPolicy;
use AerialShip\LightSaml\Protocol;

class NameIDFormat implements GetXmlInterface, LoadFromXmlInterface
{
    /** @var  string */
    protected $value;


    /**
     * @param string $value
     */
    public function __construct($value = null)
    {
        $this->value = $value;
    }

    /**
     * @param string $value
     * @throws \InvalidArgumentException
     * @return $this|NameIDFormat
     */
    public function setValue($value)
    {
        $value = trim($value);
        if ($value && false == NameIDPolicy::isValid($value)) {
            throw new \InvalidArgumentException(sprintf("Invalid NameIDFormat '%s'", $value));
        }
        $this->value = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }



    /**
     * @param \DOMNode $parent
     * @param SerializationContext $context
     * @return \DOMElement
     */
    public function getXml(\DOMNode $parent, SerializationContext $context)
    {
        $result = $context->getDocument()->createElementNS(Protocol::NS_METADATA, 'md:NameIDFormat');
        $parent->appendChild($result);

        $result->nodeValue = $this->value;

        return $result;
    }

    /**
     * @param \DOMElement $xml
     * @throws \AerialShip\LightSaml\Error\InvalidXmlException
     * @return void
     */
    public function loadFromXml(\DOMElement $xml)
    {
        if ($xml->localName != 'NameIDFormat' || $xml->namespaceURI != Protocol::NS_METADATA) {
            throw new InvalidXmlException('Expected NameIDFormat element and '.Protocol::NS_METADATA.' namespace but got '.$xml->localName);
        }

        $this->setValue($xml->nodeValue);
    }

} 