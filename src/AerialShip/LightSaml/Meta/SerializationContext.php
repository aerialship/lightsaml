<?php

namespace AerialShip\LightSaml\Meta;


class SerializationContext
{
    /** @var \DOMDocument */
    protected $document;


    function __construct(\DOMDocument $document = null) {
        $this->document = $document ? $document : new \DOMDocument();
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