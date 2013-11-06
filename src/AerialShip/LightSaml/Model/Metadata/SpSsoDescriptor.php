<?php

namespace AerialShip\LightSaml\Model\Metadata;

use AerialShip\LightSaml\Helper;
use AerialShip\LightSaml\Meta\SerializationContext;
use AerialShip\LightSaml\Model\Metadata\Service\AbstractService;


class SpSsoDescriptor extends SSODescriptor
{
    /** @var bool */
    protected $wantAssertionsSigned = false;


    /**
     * @param boolean $wantAssertionsSigned
     */
    public function setWantAssertionsSigned($wantAssertionsSigned) {
        $this->wantAssertionsSigned = (bool)$wantAssertionsSigned;
    }

    /**
     * @return boolean
     */
    public function getWantAssertionsSigned() {
        return $this->wantAssertionsSigned;
    }






    public function addService(AbstractService $service) {
        $class = Helper::getClassNameOnly($service);
        if ($class != 'SingleLogoutService' &&
            $class != 'AssertionConsumerService'
        ) {
            throw new \InvalidArgumentException("Invalid service type $class for SPSSODescriptor");
        }
        return parent::addService($service);
    }

    /**
     * @return string
     */
    public function getXmlNodeName() {
        return 'SPSSODescriptor';
    }


    /**
     * @param \DOMNode $parent
     * @param SerializationContext $context
     * @return \DOMElement
     */
    function getXml(\DOMNode $parent, SerializationContext $context) {
        $result = parent::getXml($parent, $context);
        $result->setAttribute('WantAssertionsSigned', $this->getWantAssertionsSigned() ? 'true' : 'false');
        return $result;
    }


    /**
     * @param \DOMElement $xml
     */
    function loadFromXml(\DOMElement $xml) {
        parent::loadFromXml($xml);
        if ($xml->hasAttribute('WantAssertionsSigned')) {
            $this->setWantAssertionsSigned($xml->getAttribute('WantAssertionsSigned'));
        }
    }


}