<?php

namespace AerialShip\LightSaml\EntityDescriptor\SP;

use AerialShip\LightSaml\Binding;

class SingleLogoutServiceItem extends SpSsoDescriptorItem
{

    function __construct($binding, $location) {
        Binding::validate($binding);
        $this->binding = $binding;
        $this->location = $location;
    }



    /**
     * @return string
     */
    public function toXmlString() {
        $binding = htmlspecialchars($this->getBinding());
        $location = htmlspecialchars($this->getLocation());
        $result = "<md:SingleLogoutService Binding=\"{$binding}\" Location=\"{$location}\" />\n";
        return $result;
    }


}