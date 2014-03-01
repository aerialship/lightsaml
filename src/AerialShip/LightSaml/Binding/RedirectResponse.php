<?php

namespace AerialShip\LightSaml\Binding;


class RedirectResponse extends Response
{

    /**
     * @param string $destination
     */
    function __construct($destination) {
        parent::__construct($destination);
    }



    public function render()
    {
        header('Location: ' . $this->getDestination(), true, 302);
        header('Pragma: no-cache');
        header('Cache-Control: no-cache, must-revalidate');
    }
}