<?php

namespace AerialShip\LightSaml\Model\XmlDSig;


interface SignatureValidatorInterface
{
    /**
     * @param \XMLSecurityKey $key
     * @return bool True if validated, False if validation was not performed
     * @throws \AerialShip\LightSaml\Error\SecurityException If validation fails
     */
    public function validate(\XMLSecurityKey $key);

    /**
     * @param \XMLSecurityKey[] $keys
     * @return bool True if validated, False if validation was not performed
     * @throws \AerialShip\LightSaml\Error\SecurityException If validation fails
     * @throws \InvalidArgumentException If some element of $keys array is not \XMLSecurityKey
     */
    public function validateMulti(array $keys);

}
