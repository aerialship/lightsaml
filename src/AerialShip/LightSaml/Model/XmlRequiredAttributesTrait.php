<?php

namespace AerialShip\LightSaml\Model;


trait XmlRequiredAttributesTrait
{
    function checkRequiredAttributes(\DOMElement $element, array $attributes) {
        foreach ($attributes as $name) {
            if (!$element->hasAttribute($name)) {
                throw new \InvalidXmlException('XML Element '.$element->localName.' missing required attribute '.$name);
            }
        }
    }
}