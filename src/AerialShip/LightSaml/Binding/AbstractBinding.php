<?php

namespace AerialShip\LightSaml\Binding;


use AerialShip\LightSaml\Meta\GetXmlInterface;
use AerialShip\LightSaml\Meta\LoadFromXmlInterface;

abstract class AbstractBinding
{
    /** @var string */
    protected $destination;





    /**
     * @param string $destination
     */
    public function setDestination($destination) {
        $this->destination = $destination;
    }

    /**
     * @return string
     */
    public function getDestination() {
        return $this->destination;
    }







    /**
     * @param GetXmlInterface $message
     * @return void
     */
    abstract function send(GetXmlInterface $message);


    /**
     * @return LoadFromXmlInterface
     */
    abstract function receive();

} 