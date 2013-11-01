<?php

namespace AerialShip\LightSaml\Model\Metadata;

use AerialShip\LightSaml\Helper;
use AerialShip\LightSaml\Model\Metadata\Service\AbstractService;


class IdpSsoDescriptor extends SSODescriptor
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