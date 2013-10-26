<?php

namespace AerialShip\LightSaml\Model\Service;

use AerialShip\LightSaml\Binding;
use AerialShip\LightSaml\Error\InvalidXmlException;
use AerialShip\LightSaml\Model\GetXmlInterface;
use AerialShip\LightSaml\Model\LoadFromXmlInterface;


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



    /**
     * @param \DOMElement $xml
     * @throws \AerialShip\LightSaml\Error\InvalidXmlException
     * @return \DOMElement[]
     */
    function loadFromXml(\DOMElement $xml) {
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