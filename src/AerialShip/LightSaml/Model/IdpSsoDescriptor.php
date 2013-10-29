<?php

namespace AerialShip\LightSaml\Model;


use AerialShip\LightSaml\Helper;
use AerialShip\LightSaml\Model\Service\AbstractService;
use AerialShip\LightSaml\Model\Service\SingleLogoutService;

class IdpSsoDescriptor extends AbstractDescriptor
{
    public function addService(AbstractService $service) {
        $class = Helper::getClassNameOnly($service);
        if ($class != 'SingleLogoutService' &&
            $class != 'SingleSignOnService'
        ) {
            throw new \InvalidArgumentException("Invalid service type $class for IDPSSODescriptor");
        }
        return parent::addService($service);
    }


    /**
     * @return string
     */
    public function getXmlNodeName() {
        return 'IDPSSODescriptor';
    }


}