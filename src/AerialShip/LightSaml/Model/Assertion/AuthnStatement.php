<?php

namespace AerialShip\LightSaml\Model;

use AerialShip\LightSaml\Meta\GetXmlInterface;
use AerialShip\LightSaml\Meta\LoadFromXmlInterface;
use AerialShip\LightSaml\Protocol;


class AuthnStatement implements GetXmlInterface, LoadFromXmlInterface
{
    /** @var int */
    protected $authnInstant;

    /** @var string */
    protected $sessionIndex;

    /** @var string */
    protected $authnContext = Protocol::AC_UNSPECIFIED;




    /**
     * @param string $authnContext
     */
    public function setAuthnContext($authnContext) {
        $this->authnContext = $authnContext;
    }

    /**
     * @return string
     */
    public function getAuthnContext() {
        return $this->authnContext;
    }

    /**
     * @param int $authnInstant
     */
    public function setAuthnInstant($authnInstant) {
        $this->authnInstant = $authnInstant;
    }

    /**
     * @return int
     */
    public function getAuthnInstant() {
        return $this->authnInstant;
    }

    /**
     * @param string $sessionIndex
     */
    public function setSessionIndex($sessionIndex) {
        $this->sessionIndex = $sessionIndex;
    }

    /**
     * @return string
     */
    public function getSessionIndex() {
        return $this->sessionIndex;
    }



    protected function prepareForXml() {
        if (!$this->getAuthnInstant()) {
            $this->setAuthnInstant(time());
        }
    }


    /**
     * @param \DOMNode $parent
     * @return \DOMElement
     */
    function getXml(\DOMNode $parent) {
        $doc = $parent instanceof \DOMDocument ? $parent : $parent->ownerDocument;
        $result = $doc->createElement('AuthnStatement');
        $parent->appendChild($result);

        $result->setAttribute('AuthnInstant', gmdate('Y-m-d\TH:i:s\Z', $this->getAuthnInstant()));
        if ($this->getSessionIndex()) {
            $result->setAttribute('SessionIndex', $this->getSessionIndex());
        }

        $authnContextNode = $doc->createElement('AuthnContext');
        $result->appendChild($authnContextNode);
        $refNode = $doc->createElement('AuthnContextClassRef', $this->getAuthnContext());
        $authnContextNode->appendChild($refNode);

        return $result;
    }

    /**
     * @param \DOMElement $xml
     * @return \DOMElement[]
     */
    function loadFromXml(\DOMElement $xml) {
        // TODO: Implement loadFromXml() method.
    }


}