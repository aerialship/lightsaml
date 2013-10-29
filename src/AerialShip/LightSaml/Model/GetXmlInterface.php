<?php

namespace AerialShip\LightSaml\Model;


interface GetXmlInterface
{
    /**
     * @param \DOMNode $parent
     * @return \DOMElement
     */
    function getXml(\DOMNode $parent);
}