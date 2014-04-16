<?php

namespace AerialShip\LightSaml\Tests\Meta;

use AerialShip\LightSaml\Bindings;
use AerialShip\LightSaml\Meta\AuthnRequestBuilder;
use AerialShip\LightSaml\Meta\SpMeta;
use AerialShip\LightSaml\Model\Metadata\EntityDescriptor;
use AerialShip\LightSaml\Model\Metadata\IdpSsoDescriptor;
use AerialShip\LightSaml\Model\Metadata\Service\AssertionConsumerService;
use AerialShip\LightSaml\Model\Metadata\Service\SingleSignOnService;
use AerialShip\LightSaml\Model\Metadata\SpSsoDescriptor;
use AerialShip\LightSaml\Model\XmlDSig\SignatureCreator;
use AerialShip\LightSaml\Security\X509Certificate;

/**
 * https://github.com/aerialship/lightsaml/issues/20
 */
class AuthnRequestBuilderTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider provider
     */
    public function testAuthnRequestBuilder($name, array $idpData, array $spData, array $spMetaData,
            $expectedSendUrl, $expectedResponseType, $expectedReceiveUrl, $expectedReceiveBinding,
            $expectedException = null, $expectedExceptionMessage = ''
    ) {
        if ($expectedException) {
            $this->setExpectedException($expectedException, $expectedExceptionMessage);
        }

        $idp = new IdpSsoDescriptor();
        foreach ($idpData as $data) {
            $idp->addService(new SingleSignOnService($data['binding'], $data['url']));
        }
        $edIDP = new EntityDescriptor('idp');
        $edIDP->addItem($idp);

        $sp = new SpSsoDescriptor();
        foreach ($spData as $data) {
            $sp->addService(new AssertionConsumerService($data['binding'], $data['url']));
        }
        $edSP = new EntityDescriptor('sp');
        $edSP->addItem($sp);

        $spMeta = new SpMeta();
        foreach ($spMetaData as $name=>$value) {
            $spMeta->{$name}($value);
        }

        // without signing
        $builder = new AuthnRequestBuilder($edSP, $edIDP, $spMeta);

        $message = $builder->build();
        $response = $builder->send($message);

        $this->assertStringStartsWith($expectedSendUrl, $response->getDestination(), $name);
        $this->assertInstanceOf($expectedResponseType, $response, $name);

        $this->assertEquals($expectedReceiveUrl, $message->getAssertionConsumerServiceURL(), $name);
        $this->assertEquals($expectedReceiveBinding, $message->getProtocolBinding(), $name);

        // with signing
        $signature = new SignatureCreator();

        $certificate = new X509Certificate();
        $certificate->loadFromFile(__DIR__ . '/../../../../../resources/sample/Certificate/saml.crt');

        $key = new \XMLSecurityKey(\XMLSecurityKey::RSA_SHA1, array('type' => 'private'));
        $key->loadKey(__DIR__ . '/../../../../../resources/sample/Certificate/saml.pem', true);

        $signature->setCertificate($certificate);
        $signature->setXmlSecurityKey($key);

        $builder = new AuthnRequestBuilder($edSP, $edIDP, $spMeta, $signature);

        $message = $builder->build();
        $response = $builder->send($message);

        $this->assertStringStartsWith($expectedSendUrl, $response->getDestination(), $name);
        $this->assertInstanceOf($expectedResponseType, $response, $name);

        $this->assertEquals($expectedReceiveUrl, $message->getAssertionConsumerServiceURL(), $name);
        $this->assertEquals($expectedReceiveBinding, $message->getProtocolBinding(), $name);
    }

    public function provider()
    {
        return array(
            array(
                'CASE 1',
                // idp
                array(
                    array('binding'=>Bindings::SAML2_HTTP_REDIRECT, 'url'=>'sso1')
                ),
                // sp
                array(
                        array('binding'=>Bindings::SAML2_HTTP_POST, 'url'=>'acs1')
                ),
                // spMeta
                array(),
                'sso1',
                'AerialShip\LightSaml\Binding\RedirectResponse',
                'acs1',
                Bindings::SAML2_HTTP_POST
            ),
            array(
                'CASE 2',
                // idp
                array(
                    array('binding'=>Bindings::SAML2_HTTP_REDIRECT, 'url'=>'sso1'),
                    array('binding'=>Bindings::SAML2_SOAP, 'url'=>'sso2')
                ),
                // sp
                array(
                    array('binding'=>Bindings::SAML2_HTTP_POST, 'url'=>'acs1'),
                    array('binding'=>Bindings::SAML2_HTTP_POST_SIMPLE_SIGN, 'url'=>'acs2')
                ),
                // spMeta
                array(),
                'sso1',
                'AerialShip\LightSaml\Binding\RedirectResponse',
                'acs1',
                Bindings::SAML2_HTTP_POST
            ),
            array(
                'CASE 3-1',
                // idp
                array(
                    array('binding'=>Bindings::SAML2_SOAP, 'url'=>'sso1'),
                    array('binding'=>Bindings::SAML2_HTTP_REDIRECT, 'url'=>'sso2')
                ),
                // sp
                array(
                    array('binding'=>Bindings::SAML2_HTTP_POST, 'url'=>'acs1'),
                    array('binding'=>Bindings::SAML2_HTTP_POST_SIMPLE_SIGN, 'url'=>'acs2')
                ),
                // spMeta
                array(
                    'setAuthnRequestBinding' => Bindings::SAML2_HTTP_REDIRECT
                ),
                'sso2',
                'AerialShip\LightSaml\Binding\RedirectResponse',
                'acs1',
                Bindings::SAML2_HTTP_POST
            ),
            array(
                'CASE 3-2',
                // idp
                array(
                    array('binding'=>Bindings::SAML2_SOAP, 'url'=>'sso1'),
                    array('binding'=>Bindings::SAML2_HTTP_REDIRECT, 'url'=>'sso2')
                ),
                // sp
                array(
                    array('binding'=>Bindings::SAML2_HTTP_POST_SIMPLE_SIGN, 'url'=>'acs1'),
                    array('binding'=>Bindings::SAML2_HTTP_POST, 'url'=>'acs2')
                ),
                // spMeta
                array(
                    'setAuthnRequestBinding' => Bindings::SAML2_HTTP_REDIRECT,
                    'setResponseBinding' => Bindings::SAML2_HTTP_POST
                ),
                'sso2',
                'AerialShip\LightSaml\Binding\RedirectResponse',
                'acs2',
                Bindings::SAML2_HTTP_POST
            ),
            array(
                'CASE 4',
                // idp
                array(
                    array('binding'=>Bindings::SAML2_SOAP, 'url'=>'sso1'),
                    array('binding'=>Bindings::SAML2_HTTP_REDIRECT, 'url'=>'sso2')
                ),
                // sp
                array(
                    array('binding'=>Bindings::SAML2_HTTP_POST_SIMPLE_SIGN, 'url'=>'acs1'),
                    array('binding'=>Bindings::SAML2_HTTP_POST, 'url'=>'acs2')
                ),
                // spMeta
                array(
                    'setAuthnRequestBinding' => Bindings::SAML2_HTTP_ARTIFACT
                ),
                null,
                null,
                null,
                null,
                'AerialShip\LightSaml\Error\BuildRequestException',
                'IDPSSODescriptor does not have SingleSignOnService with binding urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Artifact'
            ),
            array(
                'CASE 5',
                // idp
                array(
                    array('binding'=>Bindings::SAML2_SOAP, 'url'=>'sso1'),
                    array('binding'=>Bindings::SAML2_HTTP_REDIRECT, 'url'=>'sso2')
                ),
                // sp
                array(
                    array('binding'=>Bindings::SAML2_HTTP_POST_SIMPLE_SIGN, 'url'=>'acs1'),
                    array('binding'=>Bindings::SAML2_HTTP_POST, 'url'=>'acs2')
                ),
                // spMeta
                array(
                    'setResponseBinding' => Bindings::SAML2_HTTP_ARTIFACT
                ),
                null,
                null,
                null,
                null,
                'AerialShip\LightSaml\Error\BuildRequestException',
                'SPSSODescriptor does not have AssertionConsumerService with binding urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Artifact'
            ),
            array(
                'CASE 6',
                // idp
                array(),
                // sp
                array(
                    array('binding'=>Bindings::SAML2_HTTP_POST_SIMPLE_SIGN, 'url'=>'acs1'),
                    array('binding'=>Bindings::SAML2_HTTP_POST, 'url'=>'acs2')
                ),
                // spMeta
                array(
                    'setAuthnRequestBinding' => Bindings::SAML2_HTTP_ARTIFACT
                ),
                null,
                null,
                null,
                null,
                'AerialShip\LightSaml\Error\BuildRequestException',
                'IDPSSODescriptor does not have any SingleSignOnService'
            ),
            array(
                'CASE 7',
                // idp
                array(
                    array('binding'=>Bindings::SAML2_SOAP, 'url'=>'sso1'),
                    array('binding'=>Bindings::SAML2_HTTP_REDIRECT, 'url'=>'sso2')
                ),
                // sp
                array(),
                // spMeta
                array(
                    'setResponseBinding' => Bindings::SAML2_HTTP_ARTIFACT
                ),
                null,
                null,
                null,
                null,
                'AerialShip\LightSaml\Error\BuildRequestException',
                'SPSSODescriptor does not have any AssertionConsumerService'
            )
        );
    }


}