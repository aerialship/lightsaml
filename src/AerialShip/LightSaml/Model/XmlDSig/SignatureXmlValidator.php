<?php

namespace AerialShip\LightSaml\Model\XmlDSig;

use AerialShip\LightSaml\Error\InvalidXmlException;
use AerialShip\LightSaml\Error\SecurityException;
use AerialShip\LightSaml\Meta\LoadFromXmlInterface;
use AerialShip\LightSaml\Protocol;
use AerialShip\LightSaml\Security\KeyHelper;


class SignatureXmlValidator extends Signature implements LoadFromXmlInterface, SignatureValidatorInterface
{
    /** @var \XMLSecurityDSig */
    protected $signature = null;

    /** @var string[] */
    protected $certificates;


    /**
     * @param \string[] $certificates
     */
    public function setCertificates($certificates)
    {
        $this->certificates = $certificates;
    }

    /**
     * @return \string[]
     */
    public function getCertificates()
    {
        return $this->certificates;
    }

    /**
     * @param \XMLSecurityDSig $signature
     */
    public function setSignature($signature)
    {
        $this->signature = $signature;
    }

    /**
     * @return \XMLSecurityDSig
     */
    public function getSignature()
    {
        return $this->signature;
    }






    /**
     * @param \DOMElement $xml
     * @throws \AerialShip\LightSaml\Error\SecurityException
     * @throws \AerialShip\LightSaml\Error\InvalidXmlException
     */
    public function loadFromXml(\DOMElement $xml)
    {
        if ($xml->localName != 'Signature' || $xml->namespaceURI != Protocol::NS_XMLDSIG) {
            throw new InvalidXmlException('Expected Signature element and '.Protocol::NS_XMLDSIG.' namespace but got '.$xml->localName);
        }
        $this->signature = new \XMLSecurityDSig();
        $this->signature->idKeys[] = $this->getIDName();
        $this->signature->sigNode = $xml;
        $this->signature->canonicalizeSignedInfo();

        if (!$this->signature->validateReference()) {
            throw new SecurityException('Digest validation failed');
        }

        $this->certificates = array();
        $xpath = new \DOMXPath($xml instanceof \DOMDocument ? $xml : $xml->ownerDocument);
        $xpath->registerNamespace('ds', Protocol::NS_XMLDSIG);
        $list = $xpath->query('./ds:KeyInfo/ds:X509Data/ds:X509Certificate', $this->signature->sigNode);
        foreach ($list as $certNode) {
            $certData = trim($certNode->textContent);
            $certData = str_replace(array("\r", "\n", "\t", ' '), '', $certData);
            $this->certificates[] = $certData;
        }
    }


    /**
     * @param \XMLSecurityKey $key
     * @return bool
     * @throws \AerialShip\LightSaml\Error\SecurityException
     */
    public function validate(\XMLSecurityKey $key)
    {
        if ($this->signature == null) {
            return false;
        }
        if ($key->type != \XMLSecurityKey::RSA_SHA1) {
            throw new SecurityException('Key type must be RSA_SHA1 but got '.$key->type);
        }

        $key = $this->castKeyIfNecessary($key);

        $ok = $this->signature->verify($key);
        if (!$ok) {
            throw new SecurityException('Unable to verify Signature');
        }
        return true;
    }


    /**
     * @param \XMLSecurityKey[] $keys
     * @throws \LogicException
     * @throws \InvalidArgumentException If some element of $keys array is not \XMLSecurityKey
     * @throws \AerialShip\LightSaml\Error\SecurityException If validation fails
     * @throws \Exception
     * @throws null
     * @return bool True if validated, False if validation was not performed
     */
    public function validateMulti(array $keys)
    {
        $lastException = null;

        foreach ($keys as $key) {

            if (!$key instanceof \XMLSecurityKey) {
                throw new \InvalidArgumentException('Expected XMLSecurityKey but got '.get_class($key));
            }

            try {
                $result = $this->validate($key);

                if ($result === false) {
                    return false;
                }

                return true;

            } catch (SecurityException $ex) {
                $lastException = $ex;
            }
        }

        if ($lastException) {
            throw $lastException;
        } else {
            throw new \LogicException('Should not get here???');
        }
    }

    /**
     * @param \XMLSecurityKey $key
     * @return \XMLSecurityKey
     */
    private function castKeyIfNecessary(\XMLSecurityKey $key)
    {
        $algorithm = $this->getAlgorithm();
        if ($key->type === \XMLSecurityKey::RSA_SHA1 && $algorithm !== $key->type) {
            $key = KeyHelper::castKey($key, $algorithm);
        }

        return $key;
    }

    /**
     * @return string
     * @throws \AerialShip\LightSaml\Error\InvalidXmlException
     */
    private function getAlgorithm()
    {
        $xpath = new \DOMXPath($this->signature->sigNode instanceof \DOMDocument ? $this->signature->sigNode : $this->signature->sigNode->ownerDocument);
        $xpath->registerNamespace('ds', \XMLSecurityDSig::XMLDSIGNS);

        $list = $xpath->query('./ds:SignedInfo/ds:SignatureMethod', $this->signature->sigNode);
        if (!$list || $list->length == 0) {
            throw new InvalidXmlException('Missing SignatureMethod element');
        }
        /** @var $sigMethod \DOMElement */
        $sigMethod = $list->item(0);
        if (!$sigMethod->hasAttribute('Algorithm')) {
            throw new InvalidXmlException('Missing Algorithm-attribute on SignatureMethod element.');
        }
        $algorithm = $sigMethod->getAttribute('Algorithm');

        return $algorithm;
    }

}