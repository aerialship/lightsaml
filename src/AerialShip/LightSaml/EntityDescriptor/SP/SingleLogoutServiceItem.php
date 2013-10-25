<?php

namespace AerialShip\LightSaml\EntityDescriptor\SP;

use AerialShip\LightSaml\Binding;
use AerialShip\LightSaml\Error\InvalidXmlException;

class SingleLogoutServiceItem extends SpSsoDescriptorItem
{

    function __construct($binding = null, $location = null) {
        if ($binding) {
            $this->setBinding($binding);
        }
        if ($location) {
            $this->setLocation($location);
        }
    }



    /**
     * @return string
     */
    public function toXmlString() {
        $binding = htmlspecialchars($this->getBinding());
        $location = htmlspecialchars($this->getLocation());
        $result = "    <md:SingleLogoutService Binding=\"{$binding}\" Location=\"{$location}\" />\n";
        return $result;
    }

    /**
     * @param \DOMElement $root
     * @throws \AerialShip\LightSaml\Error\InvalidXmlException
     * @return \DOMElement[] unknown elements
     */
    public function loadXml(\DOMElement $root) {
        if (!$root->hasAttribute('Binding')) {
            throw new InvalidXmlException("Missing Binding attribute");
        }
        if (!$root->hasAttribute('Location')) {
            throw new InvalidXmlException("Missing Location attribute");
        }
        $this->setBinding($root->getAttribute('Binding'));
        $this->setLocation($root->getAttribute('Location'));
        return array();
    }


}