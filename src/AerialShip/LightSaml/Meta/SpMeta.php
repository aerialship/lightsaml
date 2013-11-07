<?php

namespace AerialShip\LightSaml\Meta;


use AerialShip\LightSaml\NameIDPolicy;

class SpMeta
{
    /** @var string */
    protected $nameIdFormat = NameIDPolicy::PERSISTENT;

    /** @var string */
    protected $authnRequestBinding;



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

    /**
     * @param string $authnRequestBinding
     */
    public function setAuthnRequestBinding($authnRequestBinding) {
        $this->authnRequestBinding = $authnRequestBinding;
    }

    /**
     * @return string
     */
    public function getAuthnRequestBinding() {
        return $this->authnRequestBinding;
    }




}
