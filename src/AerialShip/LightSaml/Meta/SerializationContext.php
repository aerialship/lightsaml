<?php

namespace AerialShip\LightSaml\Meta;


class SerializationContext
{
    /** @var bool  */
    protected $sign = false;

    /** @var \DOMDocument */
    protected $document;


    function __construct(\DOMDocument $document = null) {
        $this->document = $document ? $document : new \DOMDocument();
    }

    /**
     * @param boolean $sign
     */
    public function setSign($sign) {
        $this->sign = $sign;
    }

    /**
     * @return boolean
     */
    public function getSign() {
        return $this->sign;
    }


    /**
     * @param \DOMDocument $document
     */
    public function setDocument($document) {
        $this->document = $document;
    }

    /**
     * @return \DOMDocument
     */
    public function getDocument() {
        return $this->document;
    }




} 