<?php

namespace AerialShip\LightSaml\Model\Metadata;

use AerialShip\LightSaml\Error\InvalidXmlException;
use AerialShip\LightSaml\Helper;
use AerialShip\LightSaml\Meta\GetXmlInterface;
use AerialShip\LightSaml\Meta\LoadFromXmlInterface;
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
     * @return GetXmlInterface[]|LoadFromXmlInterface[]
     */
    public function getItemsByType($class) {
        $result = array();
        foreach ($this->items as $item) {
            if (Helper::doClassNameMatch($item, $class)) {
                $result[] = $item;
            }
        }
        return $result;
    }

    /**
     * @return SpSsoDescriptor[]
     */
    public function getSpSsoDescriptors() {
        $result = $this->getItemsByType('AerialShip\LightSaml\Model\Metadata\SpSsoDescriptor');
        return $result;
    }

    /**
     * @return IdpSsoDescriptor[]
     */
    public function getIdpSsoDescriptors() {
        $result = $this->getItemsByType('AerialShip\LightSaml\Model\Metadata\IdpSsoDescriptor');
        return $result;
    }

    /**
     * @param \DOMNode $parent
     * @return \DOMElement
     */
    function getXml(\DOMNode $parent) {
        /** @var $doc \DOMDocument */
        $doc = $parent instanceof \DOMDocument ? $parent : $parent->ownerDocument;
        $result = $doc->createElementNS(Protocol::NS_METADATA, 'md:EntityDescriptor');
        $result->setAttribute('entityID', $this->getEntityID());
        $parent->appendChild($result);
        foreach ($this->items as $item) {
            $item->getXml($result);
        }
        return $result;
    }

    /**
     * @param \DOMElement $xml
     * @return array|\DOMElement[]
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

        $result = $this->loadXmlChildren(
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
        return $result;
    }


    /*

        public function _loadXml(\DOMElement $root) {
            $result = array();
            if ($root->namespaceURI != Protocol::NS_METADATA)
                throw new InvalidXmlException('Expected namespace '.Protocol::NS_METADATA." found $root->namespaceURI");
            if ($root->localName != 'EntityDescriptor')
                throw new InvalidXmlException("Expected element EntityDescriptor found $root->localName");
            if (!$root->hasAttribute('entityID'))
                throw new InvalidXmlException("Missing element entityID");
            $this->entityID = $root->getAttribute('entityID');

            for ($node = $root->firstChild; $node !== NULL; $node = $node->nextSibling) {
                if ($node->namespaceURI != Protocol::NS_METADATA) {
                    continue;
                }
                $child = null;
                switch ($node->localName) {
                    case 'SPSSODescriptor':
                        $child = new SpSsoDescriptor();
                        break;
                    default:
                        $result[] = $node;
                }
                if ($child) {
                    $result = array_merge($result, $child->loadXml($node));
                    $this->addItem($child);
                }
            }
            return $result;
        }
    */

}