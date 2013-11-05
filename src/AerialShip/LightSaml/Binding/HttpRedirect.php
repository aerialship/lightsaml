<?php

namespace AerialShip\LightSaml\Binding;

use AerialShip\LightSaml\Meta\GetXmlInterface;
use AerialShip\LightSaml\Meta\LoadFromXmlInterface;


class HttpRedirect extends AbstractBinding
{
    /**
     * @param GetXmlInterface $message
     * @return void
     */
    function send(GetXmlInterface $message) {
        // TODO: Implement send() method.
    }

    /**
     * @return LoadFromXmlInterface
     */
    function receive() {
        // TODO: Implement receive() method.
    }

} 