<?php

namespace AerialShip\LightSaml\EntityDescriptor;

use AerialShip\LightSaml\EntityDescriptor\SP\SpSsoDescriptor;
use AerialShip\LightSaml\Error\InvalidXmlException;
use AerialShip\LightSaml\Protocol;


class EntityDescriptor
{
    /** @var string */
    protected $entityID;

    /** @var EntityDescriptorItem[] */
    protected $items;



    function __construct($entityID = null, array $items = null) {
        $this->entityID = $entityID;
        $this->items = $items ?: array();
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
     * @param EntityDescriptorItem[] $items
     */
    public function setItems(array $items) {
        $this->items = $items;
    }

    /**
     * @return EntityDescriptorItem[]
     */
    public function getItems() {
        return $this->items;
    }


    /**
     * @param EntityDescriptorItem $item
     * @return EntityDescriptor
     */
    public function addItem(EntityDescriptorItem $item) {
        $this->items[] = $item;
        return $this;
    }


    /**
     * @return SpSsoDescriptor|null
     */
    public function getSpSsoItem() {
        $result = null;
        foreach ($this->items as $item) {
            if ($item instanceof SpSsoDescriptor) {
                $result = $item;
                break;
            }
        }
        return $result;
    }



    /**
     * @return string
     */
    public function toXmlString() {
        $entityID = htmlspecialchars($this->getEntityID());
        $ns = Protocol::NS_METADATA;
        $result = "<?xml version=\"1.0\"?>\n<md:EntityDescriptor xmlns:md=\"{$ns}\" entityID=\"{$entityID}\">\n";
        foreach ($this->getItems() as $item) {
            $result .= $item->toXmlString();
        }
        $result .= "</md:EntityDescriptor>";
        return $result;
    }


    /**
     * @param \DOMElement $root
     * @return \DOMElement[]  Array of unknown elements that are not required
     * @throws \AerialShip\LightSaml\Error\InvalidXmlException
     */
    public function loadXml(\DOMElement $root) {
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
            /** @var $node \DOMElement */
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

}