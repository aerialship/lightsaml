<?php

namespace AerialShip\LightSaml\EntityDescriptor\SP;

use AerialShip\LightSaml\Binding;
use AerialShip\LightSaml\EntityDescriptor\EntityDescriptorItem;
use AerialShip\LightSaml\Protocol;

class SpSsoDescriptor extends EntityDescriptorItem
{
    /** @var SpSsoDescriptorItem[] */
    protected $items;



    function __construct(array $items = null) {
        $this->items = $items ?: array();
    }



    /**
     * @param SpSsoDescriptorItem[] $items
     */
    public function setItems(array $items) {
        $this->items = $items;
    }

    /**
     * @return SpSsoDescriptorItem[]
     */
    public function getItems() {
        return $this->items;
    }


    /**
     * @param SpSsoDescriptorItem $item
     * @return SpSsoDescriptor
     */
    public function addItem(SpSsoDescriptorItem $item) {
        $this->items[] = $item;
        return $this;
    }


    /**
     * @return string[]
     */
    public function getSupportedProtocols() {
        $arr = array();
        foreach ($this->getItems() as $item) {
            $protocol = Binding::getBindingProtocol($item->getBinding());
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
     * @return SingleLogoutServiceItem|null
     */
    public function getSingleLogoutItem() {
        $result = null;
        foreach ($this->items as $item) {
            if ($item instanceof SingleLogoutServiceItem) {
                $result = $item;
                break;
            }
        }
        return $result;
    }


    /**
     * @return AssertionConsumerServiceItem[]
     */
    public function getAllAssertionConsumerItems() {
        $result = array();
        foreach ($this->items as $item) {
            if ($item instanceof AssertionConsumerServiceItem) {
                $result[] = $item;
            }
        }
        return $result;
    }

    /**
     * @param string $binding
     * @return AssertionConsumerServiceItem|null
     */
    public function getAssertionConsumerItemForBinding($binding) {
        $result = null;
        foreach ($this->items as $item) {
            if ($item instanceof AssertionConsumerServiceItem && $item->getBinding() == $binding) {
                $result = $item;
                break;
            }
        }
        return $result;
    }


    /**
     * @return string
     */
    function toXmlString() {
        $protocolEnumeration = htmlspecialchars($this->getProtocolSupportEnumeration());
        $result = "  <md:SPSSODescriptor protocolSupportEnumeration=\"{$protocolEnumeration}\">\n";
        foreach ($this->getItems() as $item) {
            $result .= $item->toXmlString();
        }
        $result .= "  </md:SPSSODescriptor>\n";
        return $result;
    }


    /**
     * @param \DOMElement $root
     * @return \DOMElement[] unknown elements
     */
    public function loadXml(\DOMElement $root) {
        $result = array();
        for ($node = $root->firstChild; $node !== NULL; $node = $node->nextSibling) {
            if ($node->namespaceURI != Protocol::NS_METADATA) {
                continue;
            }
            /** @var $node \DOMElement */
            $child = null;
            switch ($node->localName) {
                case 'SingleLogoutService':
                    $child = new SingleLogoutServiceItem();
                    break;
                case 'AssertionConsumerService':
                    $child = new AssertionConsumerServiceItem();
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