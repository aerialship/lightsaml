<?php
/**
 * @desc
 * @author Ostojić Aleksandar <ao@boutsourcing.com> 11/6/13
 */

namespace AerialShip\LightSaml\Model\Protocol;


use AerialShip\LightSaml\Error\InvalidRequestException;
use AerialShip\LightSaml\Meta\SerializationContext;
use AerialShip\LightSaml\Protocol;

class LogoutRequest extends AbstractRequest{

    const FORMAT_NotOnOrAfter = 'Y-m-d\TG:i:s\Z';

    /**
     * @var DateTime|null UTC
     */
    protected $notOnOrAfter;

    /**
     * @var string|null
     */
    protected $reason;

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
     * @param DateTime $time UTC datetime
     */
    public function setNotOnOrAfter(\DateTime $time){
        if($time->getTimezone()->getName() != 'UTC'){
            throw new InvalidRequestException('Time zone must be UTC, given ['.$time->getTimezone()->getName().']');
        }
        $this->notOnOrAfter =  $time;
        return $this;
    }

    public function getNotOnOrAfter(){
        return $this->notOnOrAfter;
    }

    /**
     * An indication of the reason for the logout
     *
     * @param string $reason
     */
    public function setReason($reason){
        $this->reason = $reason;
        return $this;
    }

    public function getReason(){
        return $this->reason;
    }

    function getXml(\DOMNode $parent, SerializationContext $context){

        $result = parent::getXml($parent, $context);

        if($this->notOnOrAfter){
            $result->setAttribute('NotOnOrAfter', $this->notOnOrAfter->format(self::FORMAT_NotOnOrAfter));
        }
        if($this->reason){
            $result->setAttribute('Reason', $this->reason);
        }
        return $result;
    }

    /**
     * @param \DOMElement $xml
     * @throws \AerialShip\LightSaml\Error\InvalidXmlException
     */
    function loadFromXml(\DOMElement $xml) {
        parent::loadFromXml($xml);

        $this->iterateChildrenElements($xml, function(\DOMElement $node) {
            if ($node->localName == 'NameIDPolicy' && $node->namespaceURI == Protocol::SAML2) {
                $this->checkRequiredAttributes($node, array('Format', 'AllowCreate'));
                $this->setNameIdPolicyFormat($node->getAttribute('Format'));
                $this->setNameIdPolicyAllowCreate($node->getAttribute('AllowCreate') == 'true');
            }
        });
    }
}