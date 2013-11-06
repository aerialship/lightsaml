<?php

namespace AerialShip\LightSaml\Meta;


interface GetXmlInterface
{
    /**
     * @param \DOMNode $parent
     * @param SerializationContext $context
     * @return \DOMElement
     */
    function getXml(\DOMNode $parent, SerializationContext $context);
}