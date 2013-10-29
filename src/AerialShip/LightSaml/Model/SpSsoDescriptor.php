<?php

namespace AerialShip\LightSaml\Model;

use AerialShip\LightSaml\Helper;
use AerialShip\LightSaml\Model\Service\AbstractService;


class SpSsoDescriptor extends AbstractDescriptor
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

    function getXml(\DOMNode $parent) {
        $result = parent::getXml($parent);
        $result->setAttribute('WantAssertionsSigned', $this->getWantAssertionsSigned() ? 'true' : 'false');
        return $result;
    }

    function loadFromXml(\DOMElement $xml) {
        $result = parent::loadFromXml($xml);
        if ($xml->hasAttribute('WantAssertionsSigned')) {
            $this->setWantAssertionsSigned($xml->getAttribute('WantAssertionsSigned'));
        }
        return $result;
    }


}