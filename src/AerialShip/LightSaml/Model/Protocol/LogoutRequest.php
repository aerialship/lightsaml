<?php

namespace AerialShip\LightSaml\Model\Protocol;

use AerialShip\LightSaml\Helper;
use AerialShip\LightSaml\Meta\SerializationContext;
use AerialShip\LightSaml\Model\Assertion\NameID;
use AerialShip\LightSaml\Model\XmlDSig\SignatureXmlValidator;
use AerialShip\LightSaml\Protocol;


class LogoutRequest extends AbstractRequest
{
    /** @var int|null */
    protected $notOnOrAfter;

    /** @var string|null */
    protected $reason;

    /** @var NameID */
    protected $nameID;

    /** @var string|null */
    protected $sessionIndex;


    /**
     * @return string
     */
    function getXmlNodeLocalName() {
        return 'LogoutRequest';
    }

    /**
     * @return string|null
     */
    function getXmlNodeNamespace() {
        return Protocol::SAML2;
    }

    /**
     * The time at which the request expires, after which the recipient may discard the message.
     * The time value is encoded in UTC
     *
     * @param int|string $notOnOrAfter
     * @return LogoutRequest
     * @throws \InvalidArgumentException
     */
    public function setNotOnOrAfter($notOnOrAfter){
        if (is_string($notOnOrAfter)) {
            $notOnOrAfter = Helper::parseSAMLTime($notOnOrAfter);
        } else if (!is_int($notOnOrAfter) || $notOnOrAfter < 1) {
            throw new \InvalidArgumentException();
        }
        $this->notOnOrAfter = $notOnOrAfter;
        return $this;
    }

    /**
     * @return int
     */
    public function getNotOnOrAfter(){
        return $this->notOnOrAfter;
    }

    /**
     * An indication of the reason for the logout
     * @param string $reason
     * @return LogoutRequest
     */
    public function setReason($reason){
        $this->reason = trim($reason);
        return $this;
    }

    public function getReason(){
        return $this->reason;
    }

    /**
     * @param NameID $nameId
     * @return LogoutRequest
     */
    public function setNameID(NameID $nameId){
        $this->nameID = $nameId;
        return $this;
    }

    /**
     * @return null|NameID
     */
    public function getNameID(){
        return $this->nameID;
    }

    /**
     * @param string $sessionIndex
     * @return LogoutRequest
     */
    public function setSessionIndex($sessionIndex){
        $this->sessionIndex = $sessionIndex;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getSessionIndex(){
        return $this->sessionIndex;
    }



    function getXml(\DOMNode $parent, SerializationContext $context)
    {
        $result = parent::getXml($parent, $context);

        if ($this->getNotOnOrAfter()) {
            $result->setAttribute('NotOnOrAfter', Helper::time2string($this->getNotOnOrAfter()));
        }
        if ($this->getReason()) {
            $result->setAttribute('Reason', $this->getReason());
        }
        if ($this->getNameID()) {
            $result->appendChild($this->getNameID()->getXml($parent, $context));
        }
        if ($this->getSessionIndex()) {
            $sessionIndex = $context->getDocument()->createElementNS(Protocol::SAML2, 'samlp:SessionIndex', $this->getSessionIndex());
            $result->appendChild($sessionIndex);
        }

        return $result;
    }

    /**
     * @param \DOMElement $xml
     * @throws \AerialShip\LightSaml\Error\InvalidXmlException
     */
    function loadFromXml(\DOMElement $xml)
    {
        parent::loadFromXml($xml);

        if ($xml->hasAttribute('Reason')) {
            $this->setReason($xml->getAttribute('Reason'));
        }
        if ($xml->hasAttribute('NotOnOrAfter')) {
            $this->setNotOnOrAfter($xml->getAttribute('NotOnOrAfter'));
        }

        $signatureNode = null;
        $this->iterateChildrenElements($xml, function(\DOMElement $node) use (&$signatureNode) {
            if ($node->localName == 'NameID') {
                $nameID = new NameID();
                $nameID->loadFromXml($node);
                $this->setNameID($nameID);
            }
            if ($node->localName == 'SessionIndex') {
                $this->setSessionIndex($node->textContent);
            }

            if ($node->localName == 'Signature' && $node->namespaceURI == Protocol::NS_XMLDSIG) {
                $signatureNode = $node;
            }
        });

        if (null !== $signatureNode) {
            $signature = new SignatureXmlValidator;
            $signature->loadFromXml($signatureNode);
            $this->setSignature($signature);
        }
    }
}
