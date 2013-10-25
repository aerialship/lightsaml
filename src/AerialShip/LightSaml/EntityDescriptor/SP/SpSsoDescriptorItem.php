<?php

namespace AerialShip\LightSaml\EntityDescriptor\SP;


use AerialShip\LightSaml\Binding;

abstract class SpSsoDescriptorItem
{
    /** @var string   one of \AerialShip\LightSaml\Binding::* constants */
    protected $binding;

    /** @var string url */
    protected $location;




    function __construct($binding, $location, $protocol) {
        $this->binding = $binding;
        $this->location = $location;
        $this->protocol = $protocol;
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
     * @return string
     */
    public abstract function toXmlString();


    /**
     * @param \DOMElement $root
     * @return \DOMElement[] unknown elements
     */
    abstract public function loadXml(\DOMElement $root);
}