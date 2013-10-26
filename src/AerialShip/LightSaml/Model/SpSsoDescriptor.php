<?php

namespace AerialShip\LightSaml\Model;

use AerialShip\LightSaml\Binding;
use AerialShip\LightSaml\Error\InvalidXmlException;
use AerialShip\LightSaml\Protocol;
use AerialShip\LightSaml\Model\Service\AbstractService;
use AerialShip\LightSaml\Model\Service\AssertionConsumerService;
use AerialShip\LightSaml\Model\Service\SingleLogoutService;


class SpSsoDescriptor implements GetXmlInterface, LoadFromXmlInterface
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
     * @return SingleLogoutService[]
     */
    public function getAllSingleLogoutServices() {
        return $this->getItemsByClass($this->getServices(), '\AerialShip\LightSaml\Model\Service\SingleLogoutService');
    }


    /**
     * @return AssertionConsumerService[]
     */
    public function getAllAssertionConsumerServices() {
        return $this->getItemsByClass($this->getServices(), '\AerialShip\LightSaml\Model\Service\AssertionConsumerService');
    }

    /**
     * @param string $binding
     * @return AssertionConsumerService|null
     */
    public function getAssertionConsumerServicesForBinding($binding) {
        $result = null;
        foreach ($this->getServices() as $service) {
            if ($service instanceof AssertionConsumerService && $service->getBinding() == $binding) {
                $result = $service;
                break;
            }
        }
        return $result;
    }



    /**
     * @param \DOMNode $parent
     * @return \DOMNode
     */
    function getXml(\DOMNode $parent) {
        $result = $parent->ownerDocument->createElementNS(Protocol::NS_METADATA, 'md:SPSSODescriptor');
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
        if ($xml->localName != 'SPSSODescriptor' || $xml->namespaceURI != Protocol::NS_METADATA) {
            throw new InvalidXmlException('Expected SPSSODescriptor element and '.Protocol::NS_METADATA.' namespace but got '.$xml->localName);
        }

        $result = $this->loadXmlChildren(
            $xml,
            array(
                array(
                    'node' => array('name'=>'SingleLogoutService', 'ns'=>Protocol::NS_METADATA),
                    'class' => '\AerialShip\LightSaml\Model\Service\SingleLogoutService'
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


    /*

        public function loadXml(\DOMElement $root) {
            $result = array();
            for ($node = $root->firstChild; $node !== NULL; $node = $node->nextSibling) {
                if ($node->namespaceURI != Protocol::NS_METADATA) {
                    continue;
                }
                $child = null;
                switch ($node->localName) {
                    case 'SingleLogoutService':
                        $child = new SingleLogoutServiceItem();
                        break;
                    case 'AssertionConsumerService':
                        $child = new AssertionConsumerServiceItem();
                        break;
                    case 'KeyDescriptor':
                        $child = new KeyDescriptor();
                        break;
                    default:
                        $result[] = $node;
                }
                if ($child) {
                    $result = array_merge($result, $child->loadXml($node));
                    if ($child instanceof KeyDescriptor) {
                        $this->addKeyDescriptor($child);
                    } else {
                        $this->addItem($child);
                    }
                }
            }
            return $result;
        }
    */

}