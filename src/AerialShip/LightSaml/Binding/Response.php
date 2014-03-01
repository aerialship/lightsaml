<?php

namespace AerialShip\LightSaml\Binding;


abstract class Response
{
    /** @var  string */
    protected $destination;



    public function __construct($destination)
    {
        $this->destination = $destination;
    }



    /**
     * @param string $destination
     */
    public function setDestination($destination)
    {
        $this->destination = $destination;
    }

    /**
     * @return string
     */
    public function getDestination()
    {
        return $this->destination;
    }

} 