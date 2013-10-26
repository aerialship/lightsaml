<?php

namespace AerialShip\LightSaml\Security;


use AerialShip\LightSaml\Error\InvalidCertificateException;

class X509Certificate
{
    /** @var string */
    protected $data;


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




    function loadPem($data) {
        $pattern = '/^-----BEGIN CERTIFICATE-----([^-]*)^-----END CERTIFICATE-----/m';
        if (!preg_match($pattern, $data, $matches)) {
            throw new InvalidCertificateException('Invalid PEM encoded certificate');
        }
        $this->data = preg_replace('/\s+/', '', $matches[1]);
    }

    function loadFromFile($filename) {
        if (!is_file($filename)) {
            throw new \InvalidCertificateException("File not found $filename");
        }
        $content = file_get_contents($filename);
        $this->loadPem($content);
    }

}