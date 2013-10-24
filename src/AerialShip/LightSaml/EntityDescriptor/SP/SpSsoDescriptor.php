<?php

namespace AerialShip\LightSaml\EntityDescriptor\SP;

use AerialShip\LightSaml\Binding;
use AerialShip\LightSaml\EntityDescriptor\EntityDescriptorItem;

class SpSsoDescriptor extends EntityDescriptorItem
{
    /** @var SpSsoDescriptorItem[] */
    protected $items = array();


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


    public function addItem(SpSsoDescriptorItem $item) {
        $this->items[] = $item;
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
     * @return string
     */
    function toXmlString() {
        $protocolEnumeration = htmlspecialchars($this->getProtocolSupportEnumeration());
        $result = "<md:SPSSODescriptor protocolSupportEnumeration=\"{$protocolEnumeration}\">\n";
        foreach ($this->getItems() as $item) {
            $result .= $item->toXmlString();
        }
        $result .= "</md:SPSSODescriptor>\n";
        return $result;
    }
}