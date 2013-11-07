<?php

namespace AerialShip\LightSaml\Tests\Binding;

use AerialShip\LightSaml\Binding\HttpRedirect;
use AerialShip\LightSaml\Bindings;
use AerialShip\LightSaml\Model\Protocol\AuthnRequest;
use AerialShip\LightSaml\Model\XmlDSig\SignatureCreator;
use AerialShip\LightSaml\Model\XmlDSig\SignatureValidatorInterface;
use AerialShip\LightSaml\NameIDPolicy;
use AerialShip\LightSaml\Security\KeyHelper;
use AerialShip\LightSaml\Security\X509Certificate;
use AerialShip\LightSaml\Tests\CommonHelper;


class HttpRedirectTest extends \PHPUnit_Framework_TestCase
{
    private $destination = 'https://b1.bead.loc/adfs/ls/';
    private $relayState = 'relay state';
    private $sigAlg = \XMLSecurityKey::RSA_SHA1;
    private $ascURL = 'https://mt.evo.team/simplesaml/module.php/saml/sp/saml2-acs.php/default-sp';
    private $protocolBinding = Bindings::SAML2_HTTP_POST;
    private $issuer = 'https://mt.evo.team/simplesaml/module.php/saml/sp/metadata.php/default-sp';
    private $nameIDPolicyFormat = NameIDPolicy::PERSISTENT;


    
    function testAuthnRequest() {
        $binding = new HttpRedirect();
        $request = $this->getRequest();
        $id = $request->getID();
        $time = $request->getIssueInstant();
        $url = $binding->getRedirectURL($request);

        $pos = strpos($url, '?');
        $destination = substr($url, 0, $pos);
        $queryString = substr($url, $pos+1);

        $this->assertEquals($this->destination, $destination);

        $data = $binding->parseQuery($queryString);
        $this->checkData($data);

        /** @var AuthnRequest $request */
        $request = $binding->processData($data);
        $this->assertTrue($request instanceof AuthnRequest);
        $this->checkRequest($request, $id, $time);
    }



    private function checkRequest(AuthnRequest $request, $id, $time) {
        $this->assertEquals($id, $request->getID());
        $this->assertEquals('2.0', $request->getVersion());
        $this->assertEquals($this->destination, $request->getDestination());
        $this->assertEquals($this->ascURL, $request->getAssertionConsumerServiceURL());
        $this->assertEquals($this->protocolBinding, $request->getProtocolBinding());
        $this->assertEquals($time, $request->getIssueInstant());

        $this->assertEquals($this->issuer, $request->getIssuer());
        $this->assertEquals($this->nameIDPolicyFormat, $request->getNameIdPolicyFormat());
        $this->assertTrue($request->getNameIdPolicyAllowCreate());


        /** @var SignatureValidatorInterface $signature */
        $signature = $request->getSignature();
        $this->assertNotNull($signature);
        $this->assertTrue($signature instanceof SignatureValidatorInterface);

        $certificate = new X509Certificate();
        $certificate->loadFromFile(__DIR__.'/../../../../../resources/sample/Certificate/saml.crt');
        $key = KeyHelper::createPublicKey($certificate);
        $signature->validate($key);
    }


    private function checkData(array $data) {
        $this->assertTrue(array_key_exists('SAMLRequest', $data));
        $this->assertTrue(array_key_exists('RelayState', $data));
        $this->assertTrue(array_key_exists('SigAlg', $data));
        $this->assertTrue(array_key_exists('Signature', $data));
        $this->assertTrue(array_key_exists('SignedQuery', $data));

        $this->assertEquals($this->relayState, $data['RelayState']);
        $this->assertEquals($this->sigAlg, $data['SigAlg']);
    }

    /**
     * @return \AerialShip\LightSaml\Model\Protocol\AuthnRequest
     */
    private function getRequest() {
        $request = CommonHelper::buildAuthnRequestFromEntityDescriptors(
            __DIR__.'/../../../../../resources/sample/EntityDescriptor/sp-ed2.xml',
            __DIR__.'/../../../../../resources/sample/EntityDescriptor/idp2-ed.xml'
        );

        $certificate = new X509Certificate();
        $certificate->loadFromFile(__DIR__.'/../../../../../resources/sample/Certificate/saml.crt');

        $key = new \XMLSecurityKey(\XMLSecurityKey::RSA_SHA1, array('type'=>'private'));
        $key->loadKey(__DIR__.'/../../../../../resources/sample/Certificate/saml.pem', true, false);

        $signature = new SignatureCreator();
        $signature->setCertificate($certificate);
        $signature->setXmlSecurityKey($key);
        $request->setSignature($signature);

        $request->setRelayState($this->relayState);

        return $request;
    }
} 