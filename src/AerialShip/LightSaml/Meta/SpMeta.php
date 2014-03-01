<?php

namespace AerialShip\LightSaml\Meta;


use AerialShip\LightSaml\NameIDPolicy;

class SpMeta
{
    /** @var string */
    protected $nameIdFormat = NameIDPolicy::PERSISTENT;

    /** @var string */
    protected $authnRequestBinding;

    /** @var string */
    protected $responseBinding;

    /** @var  string */
    protected $logoutRequestBinding;


    /**
     * @param string $nameIdFormat
     * @throws \InvalidArgumentException
     */
    public function setNameIdFormat($nameIdFormat)
    {
        if (!NameIDPolicy::isValid($nameIdFormat)) {
            throw new \InvalidArgumentException('Invalid NameIDFormat '.$nameIdFormat);
        }
        $this->nameIdFormat = $nameIdFormat;
    }


    /**
     * @return string
     */
    public function getNameIdFormat()
    {
        return $this->nameIdFormat;
    }

    /**
     * @param string $authnRequestBinding
     */
    public function setAuthnRequestBinding($authnRequestBinding)
    {
        $this->authnRequestBinding = $authnRequestBinding;
    }

    /**
     * @return string
     */
    public function getAuthnRequestBinding()
    {
        return $this->authnRequestBinding;
    }

    /**
     * @param string $responseBinding
     */
    public function setResponseBinding($responseBinding)
    {
        $this->responseBinding = $responseBinding;
    }

    /**
     * @return string
     */
    public function getResponseBinding()
    {
        return $this->responseBinding;
    }

    /**
     * @param string $logoutRequestBinding
     */
    public function setLogoutRequestBinding($logoutRequestBinding)
    {
        $this->logoutRequestBinding = $logoutRequestBinding;
    }

    /**
     * @return string
     */
    public function getLogoutRequestBinding()
    {
        return $this->logoutRequestBinding;
    }



}
