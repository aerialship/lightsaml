<?php

namespace AerialShip\LightSaml\Model\Metadata;

use AerialShip\LightSaml\Bindings;
use AerialShip\LightSaml\Error\InvalidXmlException;
use AerialShip\LightSaml\Helper;
use AerialShip\LightSaml\Meta\GetXmlInterface;
use AerialShip\LightSaml\Meta\LoadFromXmlInterface;
use AerialShip\LightSaml\Meta\SerializationContext;
use AerialShip\LightSaml\Meta\XmlChildrenLoaderTrait;
use AerialShip\LightSaml\Model\Metadata\Service\AbstractService;
use AerialShip\LightSaml\Model\Metadata\Service\AssertionConsumerService;
use AerialShip\LightSaml\Model\Metadata\Service\SingleLogoutService;
use AerialShip\LightSaml\Protocol;


abstract class SSODescriptor implements GetXmlInterface, LoadFromXmlInterface
{
    use XmlChildrenLoaderTrait;


    /** @var AbstractService[] */
    protected $services;

    /** @var KeyDescriptor[] */
    protected $keyDescriptors;

    /** @var NameIDFormat[] */
    protected $nameIdFormats = array();



    public function __construct(array $services = null, array $keyDescriptors = null)
    {
        $this->services = $services ?: array();
        $this->keyDescriptors = $keyDescriptors ?: array();
    }



    /**
     * @return KeyDescriptor[]
     */
    public function getKeyDescriptors()
    {
        return $this->keyDescriptors;
    }

    /**
     * @param KeyDescriptor[] $keyDescriptors
     */
    public function setKeyDescriptors(array $keyDescriptors)
    {
        $this->keyDescriptors = $keyDescriptors;
    }


    /**
     * @param KeyDescriptor $keyDescriptor
     */
    public function addKeyDescriptor(KeyDescriptor $keyDescriptor)
    {
        $this->keyDescriptors[] = $keyDescriptor;
    }

    /**
     * @param AbstractService[] $services
     */
    public function setServices(array $services)
    {
        $this->services = $services;
    }

    /**
     * @return AbstractService[]
     */
    public function getServices()
    {
        return $this->services;
    }

    /**
     * @param AbstractService $service
     * @return SpSsoDescriptor
     */
    public function addService(AbstractService $service)
    {
        $this->services[] = $service;
        return $this;
    }

    /**
     * @return NameIDFormat[]
     */
    public function getNameIdFormats()
    {
        return $this->nameIdFormats;
    }

    /**
     * @param NameIDFormat $nameIDFormat
     */
    public function addNameIdFormat(NameIDFormat $nameIDFormat)
    {
        $this->nameIdFormats[] = $nameIDFormat;
    }

    /**
     * @param NameIDFormat[] $nameIdFormats
     */
    public function setNameIdFormats(array $nameIdFormats)
    {
        $this->nameIdFormats = $nameIdFormats;
    }


    /**
     * @return string[]
     */
    public function getSupportedProtocols()
    {
        $arr = array();
        foreach ($this->getServices() as $service) {
            $protocol = Bindings::getBindingProtocol($service->getBinding());
            $arr[$protocol] = $protocol;
        }
        return array_values($arr);
    }

    /**
     * @return string
     */
    public function getProtocolSupportEnumeration()
    {
        return join(' ', $this->getSupportedProtocols());
    }


    /**
     * @param string|null $use
     * @return KeyDescriptor[]
     */
    public function findKeyDescriptors($use)
    {
        $result = array();
        foreach ($this->getKeyDescriptors() as $kd) {
            if ($use === null || !$kd->getUse() || $kd->getUse() == $use) {
                $result[] = $kd;
            }
        }
        return $result;
    }

    /**
     * @param string $class
     * @param string|null $binding
     * @return AbstractService[]
     */
    public function findServices($class, $binding)
    {
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
    public function findSingleLogoutServices($binding = null)
    {
        return $this->findServices('AerialShip\LightSaml\Model\Metadata\Service\SingleLogoutService', $binding);
    }

    /**
     * @param string|null $binding
     * @return AssertionConsumerService[]
     */
    public function findAssertionConsumerServices($binding = null)
    {
        return $this->findServices('AerialShip\LightSaml\Model\Metadata\Service\AssertionConsumerService', $binding);
    }

    /**
     * @param string|null $binding
     * @return Service\AbstractService[]
     */
    public function findSingleSignOnServices($binding = null)
    {
        return $this->findServices('AerialShip\LightSaml\Model\Metadata\Service\SingleSignOnService', $binding);
    }


    /**
     * @return string
     */
    abstract public function getXmlNodeName();


    /**
     * @param \DOMNode $parent
     * @param \AerialShip\LightSaml\Meta\SerializationContext $context
     * @return \DOMElement
     */
    public function getXml(\DOMNode $parent, SerializationContext $context)
    {
        $result = $context->getDocument()->createElementNS(Protocol::NS_METADATA, 'md:'.$this->getXmlNodeName());
        $parent->appendChild($result);
        $result->setAttribute('protocolSupportEnumeration', $this->getProtocolSupportEnumeration());
        foreach ($this->getKeyDescriptors() as $kd) {
            $kd->getXml($result, $context);
        }
        foreach ($this->getServices() as $service) {
            $service->getXml($result, $context);
        }
        foreach ($this->getNameIdFormats() as $nameIdFormat) {
            $nameIdFormat->getXml($result, $context);
        }
        return $result;
    }


    /**
     * @param \DOMElement $xml
     * @throws \AerialShip\LightSaml\Error\InvalidXmlException
     */
    public function loadFromXml(\DOMElement $xml)
    {
        $name = $this->getXmlNodeName();
        if ($xml->localName != $name || $xml->namespaceURI != Protocol::NS_METADATA) {
            throw new InvalidXmlException("Expected $name element and ".Protocol::NS_METADATA.' namespace but got '.$xml->localName);
        }

        $this->loadXmlChildren(
            $xml,
            array(
                array(
                    'node' => array('name'=>'SingleLogoutService', 'ns'=>Protocol::NS_METADATA),
                    'class' => '\AerialShip\LightSaml\Model\Metadata\Service\SingleLogoutService'
                ),
                array(
                    'node' => array('name'=>'SingleSignOnService', 'ns'=>Protocol::NS_METADATA),
                    'class' => '\AerialShip\LightSaml\Model\Metadata\Service\SingleSignOnService'
                ),
                array(
                    'node' => array('name'=>'AssertionConsumerService', 'ns'=>Protocol::NS_METADATA),
                    'class' => '\AerialShip\LightSaml\Model\Metadata\Service\AssertionConsumerService'
                ),
                array(
                    'node' => array('name'=>'KeyDescriptor', 'ns'=>Protocol::NS_METADATA),
                    'class' => '\AerialShip\LightSaml\Model\Metadata\KeyDescriptor'
                ),
                array(
                    'node' => array('name'=>'NameIDFormat', 'ns'=>Protocol::NS_METADATA),
                    'class' => '\AerialShip\LightSaml\Model\Metadata\NameIDFormat'
                ),
            ),
            function(LoadFromXmlInterface $obj) {
                if ($obj instanceof AbstractService) {
                    $this->addService($obj);
                } else if ($obj instanceof KeyDescriptor) {
                    $this->addKeyDescriptor($obj);
                } else if ($obj instanceof NameIDFormat) {
                    $this->addNameIdFormat($obj);
                } else {
                    throw new \InvalidArgumentException('Invalid item type '.get_class($obj));
                }
            }
        );
    }
}