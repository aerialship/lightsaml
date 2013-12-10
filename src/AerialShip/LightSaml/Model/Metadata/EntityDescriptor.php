<?php

namespace AerialShip\LightSaml\Model\Metadata;

use AerialShip\LightSaml\Error\InvalidXmlException;
use AerialShip\LightSaml\Helper;
use AerialShip\LightSaml\Meta\GetXmlInterface;
use AerialShip\LightSaml\Meta\LoadFromXmlInterface;
use AerialShip\LightSaml\Meta\SerializationContext;
use AerialShip\LightSaml\Meta\XmlChildrenLoaderTrait;
use AerialShip\LightSaml\Protocol;

class EntityDescriptor implements GetXmlInterface, LoadFromXmlInterface
{
    use XmlChildrenLoaderTrait;


    /** @var string */
    protected $entityID;

    /** @var GetXmlInterface[]|LoadFromXmlInterface[] */
    protected $items;



    function __construct($entityID = null, array $items = null) {
        $this->entityID = $entityID;
        $this->items = $items ?: array();
        foreach ($this->items as $item) {
            if (!$item instanceof GetXmlInterface) {
                throw new \InvalidArgumentException('All EntityDescriptor items must implement GetXmlInterface');
            }
        }
    }


    /**
     * @param string $entityID
     */
    public function setEntityID($entityID) {
        $this->entityID = $entityID;
    }

    /**
     * @return string
     */
    public function getEntityID() {
        return $this->entityID;
    }

    /**
     * @param GetXmlInterface[]|LoadFromXmlInterface[] $items
     */
    public function setItems(array $items) {
        $this->items = $items;
    }

    /**
     * @return GetXmlInterface[]|LoadFromXmlInterface[]
     */
    public function getItems() {
        return $this->items;
    }


    /**
     * @param GetXmlInterface|LoadFromXmlInterface $item
     * @throws \InvalidArgumentException
     * @return EntityDescriptor
     */
    public function addItem($item) {
        if (!$item instanceof GetXmlInterface || !$item instanceof LoadFromXmlInterface) {
            throw new \InvalidArgumentException('Item must implement GetXmlInterface and LoadFromXmlInterface');
        }
        $this->items[] = $item;
        return $this;
    }


    /**
     * @param string $class
     * @param int|bool $count
     * @return GetXmlInterface[]|LoadFromXmlInterface[]
     */
    public function getItemsByType($class, $count = false) {
        $result = array();
        foreach ($this->items as $item) {
            if (Helper::doClassNameMatch($item, $class)) {
                $result[] = $item;
                if ($count && count($result) >= $count) {
                    break;
                }
            }
        }
        return $result;
    }

    /**
     * @return SpSsoDescriptor[]
     */
    public function getAllSpSsoDescriptors() {
        $result = $this->getItemsByType('AerialShip\LightSaml\Model\Metadata\SpSsoDescriptor');
        return $result;
    }

    /**
     * @return SpSsoDescriptor|null
     */
    public function getFirstSpSsoDescriptor() {
        $arr = $this->getItemsByType('AerialShip\LightSaml\Model\Metadata\SpSsoDescriptor', 1);
        if ($arr) {
            return array_pop($arr);
        }
        return null;
    }

    /**
     * @return IdpSsoDescriptor[]
     */
    public function getAllIdpSsoDescriptors() {
        $result = $this->getItemsByType('AerialShip\LightSaml\Model\Metadata\IdpSsoDescriptor');
        return $result;
    }

    /**
     * @return IdpSsoDescriptor|null
     */
    public function getFirstIdpSsoDescriptor() {
        $arr = $this->getItemsByType('AerialShip\LightSaml\Model\Metadata\IdpSsoDescriptor', 1);
        if ($arr) {
            return array_pop($arr);
        }
        return null;
    }

    /**
     * @param \DOMNode $parent
     * @param \AerialShip\LightSaml\Meta\SerializationContext $context
     * @return \DOMElement
     */
    function getXml(\DOMNode $parent, SerializationContext $context) {
        $result = $context->getDocument()->createElementNS(Protocol::NS_METADATA, 'md:EntityDescriptor');
        $result->setAttribute('entityID', $this->getEntityID());
        $parent->appendChild($result);
        foreach ($this->items as $item) {
            $item->getXml($result, $context);
        }
        return $result;
    }

    /**
     * @param \DOMElement $xml
     * @throws \AerialShip\LightSaml\Error\InvalidXmlException
     * @throws \Exception
     */
    function loadFromXml(\DOMElement $xml) {
        if ($xml->localName != 'EntityDescriptor' || $xml->namespaceURI != Protocol::NS_METADATA) {
            throw new InvalidXmlException('Expected EntityDescriptor element and '.Protocol::NS_METADATA.' namespace but got '.$xml->localName);
        }
        if (!$xml->hasAttribute('entityID')) {
            throw new InvalidXmlException('Missing entityID attribute');
        }
        $this->setEntityID($xml->getAttribute('entityID'));

        $this->loadXmlChildren(
            $xml,
            array(
                array(
                    'node' => array('name'=>'SPSSODescriptor', 'ns'=>Protocol::NS_METADATA),
                    'class' => '\AerialShip\LightSaml\Model\Metadata\SpSsoDescriptor'
                ),
                array(
                    'node' => array('name'=>'IDPSSODescriptor', 'ns'=>Protocol::NS_METADATA),
                    'class' => '\AerialShip\LightSaml\Model\Metadata\IdpSsoDescriptor'
                ),
            ),
            function(LoadFromXmlInterface $obj) {
                $this->addItem($obj);
            }
        );
    }



}