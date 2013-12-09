<?php

namespace AerialShip\LightSaml\Security;

use AerialShip\LightSaml\Error\SecurityException;


class KeyHelper
{
    /**
     * @param string $key  Key content or key filename
     * @param string $passphrase  Passphrase for the private key
     * @param bool $isFile  true if $key is a filename of the key
     * @param bool $isCert  true if the $key is certificate
     * @return \XMLSecurityKey
     */
    static function createPrivateKey($key, $passphrase, $isFile = false, $isCert = false)
    {
        $result = new \XMLSecurityKey(\XMLSecurityKey::RSA_SHA1, array('type'=>'private'));
        $result->passphrase = $passphrase;
        $result->loadKey($key, $isFile, $isCert);

        return $result;
    }

    /**
     * @param X509Certificate $certificate
     * @return \XMLSecurityKey
     */
    static function createPublicKey(X509Certificate $certificate)
    {
        $key = new \XMLSecurityKey(\XMLSecurityKey::RSA_SHA1, array('type'=>'public'));
        $key->loadKey($certificate->toPem(), false, true);

        return $key;
    }


    /**
     * @param \XMLSecurityKey $key
     * @param string $algorithm
     * @throws \AerialShip\LightSaml\Error\SecurityException
     * @throws \InvalidArgumentException
     * @return \XMLSecurityKey
     */
    static  function castKey(\XMLSecurityKey $key, $algorithm)
    {
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