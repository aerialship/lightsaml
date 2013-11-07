<?php

namespace AerialShip\LightSaml\Tests;


use AerialShip\LightSaml\Meta\AuthnRequestBuilder;
use AerialShip\LightSaml\Meta\SpMeta;
use AerialShip\LightSaml\Model\Metadata\EntityDescriptor;
use AerialShip\LightSaml\Model\Protocol\AuthnRequest;
use AerialShip\LightSaml\NameIDPolicy;

class CommonHelper
{

    /**
     * @param string $file
     * @return EntityDescriptor
     * @throws \InvalidArgumentException
     */
    static function getEntityDescriptorFromXmlFile($file) {
        if (!is_file($file)) {
            throw new \InvalidArgumentException("Specified EntityDescriptor path is not a file $file");
        }
        $doc = new \DOMDocument();
        $doc->load($file);
        $result = new EntityDescriptor();
        $result->loadFromXml($doc->firstChild);
        return $result;
    }


    /**
     * @param string $sp
     * @param string $idp
     * @param SpMeta $spMeta
     * @return AuthnRequest
     * @throws \InvalidArgumentException
     */
    static function buildAuthnRequestFromEntityDescriptors($sp, $idp, SpMeta $spMeta = null) {
        if (is_string($sp)) {
            $sp = self::getEntityDescriptorFromXmlFile($sp);
        } else if (!$sp instanceof EntityDescriptor) {
            throw new \InvalidArgumentException('SP parameter must be instance of EntityDescriptor or string');
        }

        if (is_string($idp)) {
            $idp = self::getEntityDescriptorFromXmlFile($idp);
        } else if (!$idp instanceof EntityDescriptor) {
            throw new \InvalidArgumentException('IDP parameter must be instance of EntityDescriptor or string');
        }

        if (!$spMeta) {
            $spMeta = new SpMeta();
            $spMeta->setNameIdFormat(NameIDPolicy::PERSISTENT);
        }

        $builder = new AuthnRequestBuilder($sp, $idp, $spMeta);
        $result = $builder->build();
        return $result;
    }

} 