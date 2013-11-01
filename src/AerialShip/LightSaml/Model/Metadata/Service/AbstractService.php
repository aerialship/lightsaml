<?php

namespace AerialShip\LightSaml\Model\Metadata\Service;

use AerialShip\LightSaml\Binding;
use AerialShip\LightSaml\Error\InvalidXmlException;
use AerialShip\LightSaml\Meta\GetXmlInterface;
use AerialShip\LightSaml\Meta\LoadFromXmlInterface;
use AerialShip\LightSaml\Protocol;


abstract class AbstractService implements GetXmlInterface, LoadFromXmlInterface
{
    /** @var string   one of \AerialShip\LightSaml\Binding constants */
    protected $binding;

    /** @var string */
    protected $location;




    function __construct($binding = null, $location = null) {
        if ($binding !== null) {
            $this->setBinding($binding);
        }
        if ($location !== null) {
            $this->setLocation($location);
        }
    }



    /**
     * @param string $binding
     */
    public function setBinding($binding) {
        Binding::validate($binding);
        $this->binding = $binding;
    }

    /**
     * @return string
     */
    public function getBinding() {
        return $this->binding;
    }

    /**
     * @param string $location
     */
    public function setLocation($location) {
        $this->location = $location;
    }

    /**
     * @return string
     */
    public function getLocation() {
        return $this->location;
    }



    abstract protected function getXmlNodeName();


    /**
     * @param \DOMNode $parent
     * @return \DOMElement|\DOMNode
     */
    function getXml(\DOMNode $parent) {
        $result = $parent->ownerDocument->createElementNS(Protocol::NS_METADATA, 'md:'.$this->getXmlNodeName());
        $parent->appendChild($result);
        $result->setAttribute('Binding', $this->getBinding());
        $result->setAttribute('Location', $this->getLocation());
        return $result;
    }


    /**
     * @param \DOMElement $xml
     * @throws \AerialShip\LightSaml\Error\InvalidXmlException
     * @return \DOMElement[]
     */
    function loadFromXml(\DOMElement $xml) {
        $name = $this->getXmlNodeName();
        if ($xml->localName != $name || $xml->namespaceURI != Protocol::NS_METADATA) {
            throw new InvalidXmlException("Expected $name element and ".Protocol::NS_METADATA.' namespace but got '.$xml->localName);
        }
        if (!$xml->hasAttribute('Binding')) {
            throw new InvalidXmlException("Missing Binding attribute");
        }
        if (!$xml->hasAttribute('Location')) {
            throw new InvalidXmlException("Missing Location attribute");
        }
        $this->setBinding($xml->getAttribute('Binding'));
        $this->setLocation($xml->getAttribute('Location'));
        return array();
    }
}