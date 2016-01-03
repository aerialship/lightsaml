<?php

namespace AerialShip\LightSaml\Meta;

use AerialShip\LightSaml\Error\BuildRequestException;
use AerialShip\LightSaml\Model\Metadata\EntityDescriptor;
use AerialShip\LightSaml\Model\Metadata\IdpSsoDescriptor;
use AerialShip\LightSaml\Model\Metadata\SpSsoDescriptor;
use AerialShip\LightSaml\Model\Protocol\Message;
use AerialShip\LightSaml\Model\XmlDSig\Signature;


abstract class AbstractRequestBuilder
{

    /** @var EntityDescriptor */
    protected $edSP;

    /** @var EntityDescriptor */
    protected $edIDP;

    /** @var \AerialShip\LightSaml\Meta\SpMeta */
    protected $spMeta;

    /** @var Signature */
    protected $signature;


    /**
     * @param EntityDescriptor $edSP
     * @param EntityDescriptor $edIDP
     * @param SpMeta $spMeta
     * @param Signature $signature
     */
    public function __construct(EntityDescriptor $edSP, EntityDescriptor $edIDP, SpMeta $spMeta, Signature $signature = null)
    {
        $this->edSP = $edSP;
        $this->edIDP = $edIDP;
        $this->spMeta = $spMeta;
        $this->signature = $signature;
    }


    /**
     * @param Message $message
     * @return \AerialShip\LightSaml\Binding\Response
     */
    abstract public function send(Message $message);


    /**
     * @param EntityDescriptor $edIDP
     */
    public function setEdIDP($edIDP) {
        $this->edIDP = $edIDP;
    }

    /**
     * @return EntityDescriptor
     */
    public function getEdIDP() {
        return $this->edIDP;
    }

    /**
     * @param EntityDescriptor $edSP
     */
    public function setEdSP($edSP) {
        $this->edSP = $edSP;
    }

    /**
     * @return EntityDescriptor
     */
    public function getEdSP() {
        return $this->edSP;
    }

    /**
     * @param Signature $signature
     */
    public function setSigningProvider($signature)
    {
        $this->signature = $signature;
    }

    /**
     * @return Signature
     */
    public function getSigningProvider()
    {
        return $this->signature;
    }


    /**
     * @return SpSsoDescriptor
     * @throws BuildRequestException
     */
    protected function getSpSsoDescriptor()
    {
        $ed = $this->getEdSP();
        if (!$ed) {
            throw new BuildRequestException('No SP EntityDescriptor set');
        }
        $arr = $ed->getAllSpSsoDescriptors();
        if (empty($arr)) {
            throw new BuildRequestException('SP EntityDescriptor has no SPSSODescriptor');
        }
        if (count($arr)>1) {
            throw new BuildRequestException('SP EntityDescriptor has more then one SPSSODescriptor');
        }
        $result = $arr[0];
        return $result;
    }


    /**
     * @return IdpSsoDescriptor
     * @throws BuildRequestException
     */
    protected function getIdpSsoDescriptor()
    {
        $ed = $this->getEdIDP();
        if (!$ed) {
            throw new BuildRequestException('No IDP EntityDescriptor set');
        }
        $arr = $ed->getAllIdpSsoDescriptors();
        if (empty($arr)) {
            throw new BuildRequestException('IDP EntityDescriptor has no IDPSSODescriptor');
        }
        if (count($arr)>1) {
            throw new BuildRequestException('IDP EntityDescriptor has more then one IDPSSODescriptor');
        }
        $result = $arr[0];
        return $result;
    }


    /**
     * @param \AerialShip\LightSaml\Model\Metadata\Service\AbstractService[] $services
     * @param string|null $binding
     * @return \AerialShip\LightSaml\Model\Metadata\Service\AbstractService|null
     */
    protected function findServiceByBinding(array $services, $binding)
    {
        $result = null;
        if (!$binding) {
            $result = array_shift($services);
        } else {
            foreach ($services as $service) {
                if ($binding == $service->getBinding()) {
                    $result = $service;
                    break;
                }
            }
        }

        return $result;
    }

} 