<?php

namespace AerialShip\LightSaml\Model;


abstract class Signature
{
    /**
     * @return string
     */
    protected function getIDName() {
        return 'ID';
    }

}