<?php

namespace AerialShip\LightSaml\Model\Protocol;

use AerialShip\LightSaml\Error\InvalidRequestException;
use AerialShip\LightSaml\Helper;
use AerialShip\LightSaml\Meta\SerializationContext;
use AerialShip\LightSaml\Model\Assertion\NameID;
use AerialShip\LightSaml\Protocol;


class LogoutRequest extends AbstractRequest
{

    const FORMAT_NOT_ON_OR_AFTER = 'Y-m-d\TH:i:s\Z';

    /**
     * @var \DateTime|null UTC
     */
    protected $notOnOrAfter;

    /** @var string|null */
    protected $reason;

    /** @var null @var NameID|null */
    protected $nameID = null;

    /** @var string|null */
    protected $sessionIndex = null;

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
     * @param \DateTime $time UTC datetime
     * @throws \AerialShip\LightSaml\Error\InvalidRequestException
     * @return LogoutRequest
     */
    public function setNotOnOrAfter(\DateTime $time){
        if($time->getTimezone()->getName() != 'UTC'){
            throw new InvalidRequestException('Time zone must be UTC, given ['.$time->getTimezone()->getName().']');
        }
        $this->notOnOrAfter =  $time;
        return $this;
    }

    /**
     * @return \DateTime|null
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
        $this->reason = $reason;
        return $this;
    }

    public function getReason(){
        return $this->reason;
    }

    /**
     * @param NameID $nameId
     * @return $this
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
     * @return $this
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

    function getXml(\DOMNode $parent, SerializationContext $context){

        $result = parent::getXml($parent, $context);

        if($this->getNotOnOrAfter()){
            $result->setAttribute('NotOnOrAfter', $this->getNotOnOrAfter()->format(self::FORMAT_NOT_ON_OR_AFTER));
        }
        if($this->getReason()){
            $result->setAttribute('Reason', $this->getReason());
        }
        if($this->getNameID()){
            $result->appendChild($this->getNameID()->getXml($parent, $context));
        }
        if($this->getSessionIndex()){
            $sessionIndex = $context->getDocument()->createElement('samlp:SessionIndex', $this->getSessionIndex());
            $result->appendChild($sessionIndex);
        }
        return $result;
    }

    /**
     * @param \DOMElement $xml
     * @throws \AerialShip\LightSaml\Error\InvalidXmlException
     */
    function loadFromXml(\DOMElement $xml) {
        parent::loadFromXml($xml);

        if($reason =$xml->getAttribute('Reason')){
            $this->setReason($reason);
        }
        if($time = $xml->getAttribute('NotOnOrAfter')){
            $time = Helper::parseSAMLTime($time);
            $this->setNotOnOrAfter(new \DateTime(strtotime($time), new \DateTimeZone('UTC')));
        }
        $this->iterateChildrenElements($xml, function(\DOMElement $node) {
            if ($node->localName == 'NameID') {
                $nameID = new NameID();
                $nameID->loadFromXml($node);
                $this->setNameID($nameID);
            }
            if ($node->localName == 'SessionIndex') {
                $this->setSessionIndex($node->textContent);
            }
        });
    }
}