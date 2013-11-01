<?php

namespace AerialShip\LightSaml\Model\XmlDSig;


abstract class Signature
{
    /**
     * @return string
     */
    protected function getIDName() {
        return 'ID';
    }

}