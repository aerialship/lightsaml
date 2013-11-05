<?php

namespace AerialShip\LightSaml\Model\Protocol;

use AerialShip\LightSaml\Error\InvalidResponseException;
use AerialShip\LightSaml\Error\InvalidXmlException;
use AerialShip\LightSaml\Helper;
use AerialShip\LightSaml\Meta\GetXmlInterface;
use AerialShip\LightSaml\Meta\LoadFromXmlInterface;
use AerialShip\LightSaml\Meta\XmlChildrenLoaderTrait;
use AerialShip\LightSaml\Meta\XmlRequiredAttributesTrait;
use AerialShip\LightSaml\Protocol;


abstract class StatusResponse extends Message
{
    /** @var string */
    protected $inResponseTo;

    /** @var Status */
    protected $status;



    /**
     * @param string $inResponseTo
     */
    public function setInResponseTo($inResponseTo) {
        $this->inResponseTo = $inResponseTo;
    }

    /**
     * @return string
     */
    public function getInResponseTo() {
        return $this->inResponseTo;
    }


    /**
     * @param \AerialShip\LightSaml\Model\Protocol\Status $status
     */
    public function setStatus($status) {
        $this->status = $status;
    }

    /**
     * @return \AerialShip\LightSaml\Model\Protocol\Status
     */
    public function getStatus() {
        return $this->status;
    }





    protected function prepareForXml() {
        parent::prepareForXml();
        if (!$this->getInResponseTo()) {
            throw new InvalidResponseException('Missing InResponseTo');
        }
        if (!$this->getStatus()) {
            throw new InvalidResponseException('Missing Status');
        }
    }


    /**
     * @param \DOMNode $parent
     * @return \DOMElement
     */
    function getXml(\DOMNode $parent) {
        $result = parent::getXml($parent);
        $result->setAttribute('InResponseTo', $this->getInResponseTo());
        $this->getStatus()->getXml($result);
        return $result;
    }

    /**
     * @param \DOMElement $xml
     * @throws \AerialShip\LightSaml\Error\InvalidXmlException
     */
    function loadFromXml(\DOMElement $xml) {
        parent::loadFromXml($xml);

        $this->checkRequiredAttributes($xml, array('InResponseTo'));
        $this->setInResponseTo($xml->getAttribute('InResponseTo'));

        $this->iterateChildrenElements($xml, function(\DOMElement $node) {
            if ($node->localName == 'Status' && $node->namespaceURI == Protocol::SAML2) {
                $this->setStatus(new Status());
                $this->getStatus()->loadFromXml($node);
            }
        });
        if (!$this->getStatus()) {
            throw new InvalidXmlException('Missing Status element');
        }
    }


}