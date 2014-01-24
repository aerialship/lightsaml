<?php

namespace AerialShip\LightSaml\Tests\Model\Metadata\EntityDescriptor;

use AerialShip\LightSaml\Bindings;
use AerialShip\LightSaml\Model\Metadata\EntityDescriptor;


/**
 * Key descriptor does not have use attribute
 *   Has formatted certificate
 * Has IDP descriptor
 *   Has SSO service
 *   Does not have SLO service
 *   Contains SHIB1 bindings
 * Does not have SP descriptor
 */
class EntityDescriptorSample02Test extends \PHPUnit_Framework_TestCase
{

    function testOne() {
        $doc = new \DOMDocument();
        $doc->load(__DIR__.'/../../../../../../../resources/sample/EntityDescriptor/ed01-formatted-certificate.xml');

        $ed = new EntityDescriptor();
        $ed->loadFromXml($doc->firstChild);

        $this->checkSP($ed);
        $this->checkIDP($ed);
    }


    private function checkIDP(EntityDescriptor $ed) {
        $arr = $ed->getAllIdpSsoDescriptors();
        $this->assertEquals(1, count($arr));
        $idp = $arr[0];

        $this->assertEquals(1, count($idp->getKeyDescriptors()));

        $arr = $idp->getKeyDescriptors();
        $this->assertEquals(1, count($arr));
        $this->assertEquals('', $arr[0]->getUse());
        $cert = $arr[0]->getCertificate();
        $this->assertNotNull($cert);
        $this->assertGreaterThan(100, strlen($cert->getData()));

        $this->assertEquals(0, count($idp->findSingleLogoutServices()));

        $this->assertEquals(4, count($idp->findSingleSignOnServices()));

        $arr = $idp->findSingleSignOnServices(Bindings::SAML2_HTTP_POST);
        $this->assertEquals(1, count($arr));
        $this->assertEquals(Bindings::SAML2_HTTP_POST, $arr[0]->getBinding());
        $this->assertEquals('https://idp.testshib.org/idp/profile/SAML2/POST/SSO', $arr[0]->getLocation());

        $arr = $idp->findSingleSignOnServices(Bindings::SAML2_HTTP_REDIRECT);
        $this->assertEquals(1, count($arr));
        $this->assertEquals(Bindings::SAML2_HTTP_REDIRECT, $arr[0]->getBinding());
        $this->assertEquals('https://idp.testshib.org/idp/profile/SAML2/Redirect/SSO', $arr[0]->getLocation());
    }


    private function checkSP(EntityDescriptor $ed) {
        $arr = $ed->getAllSpSsoDescriptors();
        $this->assertEquals(0, count($arr));
    }

}