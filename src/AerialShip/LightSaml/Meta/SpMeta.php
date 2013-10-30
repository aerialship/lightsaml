<?php

namespace AerialShip\LightSaml\Meta;


use AerialShip\LightSaml\NameIDPolicy;

class SpMeta
{
    protected $nameIdFormat = NameIDPolicy::PERSISTENT;


    /**
     * @param string $nameIdFormat
     * @throws \InvalidArgumentException
     */
    public function setNameIdFormat($nameIdFormat) {
        if (!NameIDPolicy::isValid($nameIdFormat)) {
            throw new \InvalidArgumentException('Invalid NameIDFormat '.$nameIdFormat);
        }
        $this->nameIdFormat = $nameIdFormat;
    }


    /**
     * @return string
     */
    public function getNameIdFormat() {
        return $this->nameIdFormat;
    }


}
