<?php

namespace AerialShip\LightSaml\Model;

use AerialShip\LightSaml\Protocol;

class EntityDescriptor implements GetXmlInterface
{
    /** @var string */
    protected $entityID;

    /** @var GetXmlInterface[] */
    protected $items;



    function __construct($entityID = null, array $items = null) {
        $this->entityID = $entityID;
        $this->items = $items?: array();
        foreach ($items as $item) {
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
     * @param GetXmlInterface[] $items
     */
    public function setItems(array $items) {
        $this->items = $items;
    }

    /**
     * @return GetXmlInterface[]
     */
    public function getItems() {
        return $this->items;
    }


    /**
     * @param GetXmlInterface $item
     * @return EntityDescriptor
     */
    public function addItem(GetXmlInterface $item) {
        $this->items[] = $item;
        return $this;
    }


    /**
     * @param string $class
     * @return GetXmlInterface[]
     */
    public function getItemsByType($class) {
        $result = array();
        foreach ($this->items as $item) {
            $itemClass = get_class($item);
            if ($itemClass == $class) {
                $result[] = $item;
            } else {
                if (($pos = strrpos($itemClass, '\\')) !== false) {
                    $itemClass = substr($itemClass, $pos);
                }
                if ($itemClass == $class) {
                    $result[] = $item;
                }
            }
        }
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


/*
    public function _toXmlString() {
        $entityID = htmlspecialchars($this->getEntityID());
        $ns = Protocol::NS_METADATA;
        $result = "<?xml version=\"1.0\"?><md:EntityDescriptor xmlns:md=\"{$ns}\" entityID=\"{$entityID}\">";
        foreach ($this->getItems() as $item) {
            $result .= $item->toXmlString();
        }
        $result .= '</md:EntityDescriptor>';
        return $result;
    }


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