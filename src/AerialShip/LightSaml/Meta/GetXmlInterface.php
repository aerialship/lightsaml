<?php

namespace AerialShip\LightSaml\Meta;


interface GetXmlInterface
{
    /**
     * @param \DOMNode $parent
     * @return \DOMElement
     */
    function getXml(\DOMNode $parent);
}