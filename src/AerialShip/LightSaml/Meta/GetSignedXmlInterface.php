<?php

namespace AerialShip\LightSaml\Meta;


interface GetSignedXmlInterface
{
    /**
     * @param \DOMNode $parent
     * @param SerializationContext $context
     * @return \DOMElement
     */
    function getSignedXml(\DOMNode $parent, SerializationContext $context);

}