<?php

namespace AerialShip\LightSaml\Model\Service;

use AerialShip\LightSaml\Binding;
use AerialShip\LightSaml\Model\GetXmlInterface;


abstract class AbstractService implements GetXmlInterface
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


    abstract function getXml(\DOMNode $parent);

}