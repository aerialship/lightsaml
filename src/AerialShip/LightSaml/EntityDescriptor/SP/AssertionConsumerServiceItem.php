<?php

namespace AerialShip\LightSaml\EntityDescriptor\SP;


use AerialShip\LightSaml\Binding;

class AssertionConsumerServiceItem extends SpSsoDescriptorItem
{
    /** @var int */
    protected $index;


    function __construct($binding, $location, $index) {
        Binding::validate($binding);
        if (!is_int($index)) {
            throw new \InvalidArgumentException($index);
        }
        $this->binding = $binding;
        $this->location = $location;
        $this->index = $index;
    }


    /**
     * @param int $index
     */
    public function setIndex($index) {
        $this->index = $index;
    }

    /**
     * @return int
     */
    public function getIndex() {
        return $this->index;
    }




    /**
     * @return string
     */
    public function toXmlString() {
        $binding = htmlspecialchars($this->getBinding());
        $location = htmlspecialchars($this->getLocation());
        $index = $this->getIndex();
        return "<md:AssertionConsumerService Binding=\"{$binding}\" Location=\"{$location}\" index=\"{$index}\" />\n";
    }


}