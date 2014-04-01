<?php

namespace AerialShip\LightSaml\Binding;

use AerialShip\LightSaml\Model\Protocol\Message;

abstract class AbstractBinding
{
    /** @var string */
    protected $destination;

    /** @var array  */
    protected $receiveListeners = array();

    /** @var array  */
    protected $sendListeners = array();



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
     * @param $callable
     */
    public function addReceiveListener($callable)
    {
        $this->receiveListeners[] = $callable;
    }

    /**
     * @param $callable
     */
    public function addSendListener($callable)
    {
        $this->sendListeners[] = $callable;
    }


    /**
     * @param string $messageString
     */
    protected function dispatchReceive($messageString)
    {
        foreach ($this->receiveListeners as $callable) {
            call_user_func($callable, $messageString);
        }
    }

    /**
     * @param string $messageString
     */
    protected function dispatchSend($messageString)
    {
        foreach ($this->sendListeners as $callable) {
            call_user_func($callable, $messageString);
        }
    }

    /**
     * @param Message $message
     * @return Response
     */
    abstract function send(Message $message);


    /**
     * @param Request $request
     * @return Message
     */
    abstract function receive(Request $request);

} 