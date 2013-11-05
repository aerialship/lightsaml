<?php

namespace AerialShip\LightSaml\Meta;


interface LoadFromXmlInterface
{
    /**
     * @param \DOMElement $xml
     */
    function loadFromXml(\DOMElement $xml);
}