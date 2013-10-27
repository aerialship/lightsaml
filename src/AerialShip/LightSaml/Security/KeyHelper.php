<?php

namespace AerialShip\LightSaml\Security;

use AerialShip\LightSaml\Error\SecurityException;


class KeyHelper
{
    /**
     * @param \XMLSecurityKey $key
     * @param string $algorithm
     * @throws \AerialShip\LightSaml\Error\SecurityException
     * @throws \InvalidArgumentException
     * @return \XMLSecurityKey
     */
    static  function castKey(\XMLSecurityKey $key, $algorithm) {
        if (!is_string($algorithm)) {
            throw new \InvalidArgumentException('Algorithm must be string');
        }

        // do nothing if algorithm is already the type of the key
        if ($key->type === $algorithm) {
            return $key;
        }

        $keyInfo = openssl_pkey_get_details($key->key);
        if ($keyInfo === FALSE) {
            throw new SecurityException('Unable to get key details from XMLSecurityKey.');
        }
        if (!isset($keyInfo['key'])) {
            throw new SecurityException('Missing key in public key details.');
        }

        $newKey = new \XMLSecurityKey($algorithm, array('type'=>'public'));
        $newKey->loadKey($keyInfo['key']);
        return $newKey;
    }

}