<?php

namespace AerialShip\LightSaml\Model\XmlDSig;

use AerialShip\LightSaml\Error\SecurityException;
use AerialShip\LightSaml\Security\KeyHelper;


class SignatureStringValidator extends Signature implements SignatureValidatorInterface
{
    /** @var string */
    protected $signature;

    /** @var string */
    protected $algorithm;

    /** @var string */
    protected $data;



    function __construct($signature, $algorithm, $data) {
        $this->signature = $signature;
        $this->algorithm = $algorithm;
        $this->data = $data;
    }



    /**
     * @param string $algorithm
     */
    public function setAlgorithm($algorithm) {
        $this->algorithm = $algorithm;
    }

    /**
     * @return string
     */
    public function getAlgorithm() {
        return $this->algorithm;
    }

    /**
     * @param string $data
     */
    public function setData($data) {
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function getData() {
        return $this->data;
    }

    /**
     * @param string $signature
     */
    public function setSignature($signature) {
        $this->signature = $signature;
    }

    /**
     * @return string
     */
    public function getSignature() {
        return $this->signature;
    }








    function validate(\XMLSecurityKey $key) {
        if ($this->getSignature() == null) {
            return false;
        }

        if ($key->type !== \XMLSecurityKey::RSA_SHA1) {
            throw new SecurityException('Invalid key type for validating signature on query string');
        }
        if ($key->type !== $this->getAlgorithm()) {
            $key = KeyHelper::castKey($key, $this->getAlgorithm());
        }

        $signature = base64_decode($this->getSignature());
        if (!$key->verifySignature($this->getData(), $signature)) {
            throw new SecurityException('Unable to validate signature on query string');
        }

        return true;
    }



}