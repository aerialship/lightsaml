<?php

namespace AerialShip\LightSaml\Binding;

use AerialShip\LightSaml\Model\Protocol\Message;

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
     * @param Message $message
     * @return Response
     */
    abstract function send(Message $message);


    /**
     * @return Message
     */
    abstract function receive(Request $request);

} 