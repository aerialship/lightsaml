<?php

namespace AerialShip\LightSaml\Model;

use AerialShip\LightSaml\Binding;
use AerialShip\LightSaml\Helper;
use AerialShip\LightSaml\Model\Service\AbstractService;
use AerialShip\LightSaml\Model\Service\AssertionConsumerService;
use AerialShip\LightSaml\Model\Service\SingleLogoutService;
use AerialShip\LightSaml\Protocol;


abstract class AbstractDescriptor implements GetXmlInterface, LoadFromXmlInterface
{
    use XmlChildrenLoaderTrait;
    use GetItemsByClassTrait;


    /** @var AbstractService[] */
    protected $services;

    /** @var KeyDescriptor[] */
    protected $keyDescriptors;




    function __construct(array $services = null, array $keyDescriptors = null) {
        $this->services = $services ?: array();
        $this->keyDescriptors = $keyDescriptors ?: array();
    }



    /**
     * @return KeyDescriptor[]
     */
    public function getKeyDescriptors() {
        return $this->keyDescriptors;
    }

    /**
     * @param KeyDescriptor[] $keyDescriptors
     */
    public function setKeyDescriptors(array $keyDescriptors) {
        $this->keyDescriptors = $keyDescriptors;
    }


    /**
     * @param KeyDescriptor $keyDescriptor
     */
    public function addKeyDescriptor(KeyDescriptor $keyDescriptor) {
        $this->keyDescriptors[] = $keyDescriptor;
    }

    /**
     * @param AbstractService[] $services
     */
    public function setServices(array $services) {
        $this->services = $services;
    }

    /**
     * @return \AerialShip\LightSaml\Model\Service\AbstractService[]
     */
    public function getServices() {
        return $this->services;
    }

    /**
     * @param AbstractService $service
     * @return SpSsoDescriptor
     */
    public function addService(AbstractService $service) {
        $this->services[] = $service;
        return $this;
    }


    /**
     * @return string[]
     */
    public function getSupportedProtocols() {
        $arr = array();
        foreach ($this->getServices() as $service) {
            $protocol = Binding::getBindingProtocol($service->getBinding());
            $arr[$protocol] = $protocol;
        }
        return array_values($arr);
    }

    /**
     * @return string
     */
    public function getProtocolSupportEnumeration() {
        return join(' ', $this->getSupportedProtocols());
    }


    /**
     * @param string $class
     * @param string|null $binding
     * @return AbstractService[]
     */
    function findServices($class, $binding) {
        $result = array();
        foreach ($this->getServices() as $service) {
            if (Helper::doClassNameMatch($service, $class)) {
                if (!$binding || $binding == $service->getBinding()) {
                    $result[] = $service;
                }
            }
        }
        return $result;
    }

    /**
     * @param string|null $binding
     * @return SingleLogoutService[]
     */
    public function findSingleLogoutServices($binding = null) {
        return $this->findServices('AerialShip\LightSaml\Model\Service\SingleLogoutService', $binding);
    }

    /**
     * @param string|null $binding
     * @return AssertionConsumerService[]
     */
    public function findAssertionConsumerServices($binding = null) {
        return $this->findServices('AerialShip\LightSaml\Model\Service\AssertionConsumerService', $binding);
    }

    /**
     * @param string|null $binding
     * @return Service\AbstractService[]
     */
    public function findSingleSignOnServices($binding = null) {
        return $this->findServices('AerialShip\LightSaml\Model\Service\SingleSignOnService', $binding);
    }


    /**
     * @return string
     */
    abstract public function getXmlNodeName();


    /**
     * @param \DOMNode $parent
     * @return \DOMNode
     */
    function getXml(\DOMNode $parent) {
        $result = $parent->ownerDocument->createElementNS(Protocol::NS_METADATA, 'md:'.$this->getXmlNodeName());
        $parent->appendChild($result);
        $result->setAttribute('protocolSupportEnumeration', $this->getProtocolSupportEnumeration());
        foreach ($this->getKeyDescriptors() as $kd) {
            $kd->getXml($result);
        }
        foreach ($this->getServices() as $service) {
            $service->getXml($result);
        }
        return $result;
    }


    function loadFromXml(\DOMElement $xml) {
        $name = $this->getXmlNodeName();
        if ($xml->localName != $name || $xml->namespaceURI != Protocol::NS_METADATA) {
            throw new InvalidXmlException("Expected $name element and ".Protocol::NS_METADATA.' namespace but got '.$xml->localName);
        }

        $result = $this->loadXmlChildren(
            $xml,
            array(
                array(
                    'node' => array('name'=>'SingleLogoutService', 'ns'=>Protocol::NS_METADATA),
                    'class' => '\AerialShip\LightSaml\Model\Service\SingleLogoutService'
                ),
                array(
                    'node' => array('name'=>'SingleSignOnService', 'ns'=>Protocol::NS_METADATA),
                    'class' => '\AerialShip\LightSaml\Model\Service\SingleSignOnService'
                ),
                array(
                    'node' => array('name'=>'AssertionConsumerService', 'ns'=>Protocol::NS_METADATA),
                    'class' => '\AerialShip\LightSaml\Model\Service\AssertionConsumerService'
                ),
                array(
                    'node' => array('name'=>'KeyDescriptor', 'ns'=>Protocol::NS_METADATA),
                    'class' => '\AerialShip\LightSaml\Model\KeyDescriptor'
                ),
            ),
            function(LoadFromXmlInterface $obj) {
                if ($obj instanceof AbstractService) {
                    $this->addService($obj);
                } else if ($obj instanceof KeyDescriptor) {
                    $this->addKeyDescriptor($obj);
                } else {
                    throw new \InvalidArgumentException('Invalid item type '.get_class($obj));
                }
            }
        );
        return $result;
    }
}