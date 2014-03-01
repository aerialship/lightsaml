<?php

namespace AerialShip\LightSaml\Tests\Binding;

use AerialShip\LightSaml\Binding\HttpRedirect;
use AerialShip\LightSaml\Binding\RedirectResponse;
use AerialShip\LightSaml\Binding\Request;
use AerialShip\LightSaml\Model\Protocol\AuthnRequest;


class HttpRedirectTest extends Base
{

    function testAuthnRequest() {
        $authnRequest = $this->getRequest();
        $id = $authnRequest->getID();
        $time = $authnRequest->getIssueInstant();

        $binding = new HttpRedirect();

        /** @var RedirectResponse $response */
        $response = $binding->send($authnRequest);
        $this->assertNotNull($response);
        $this->assertTrue($response instanceof RedirectResponse);
        $pos = strpos($response->getDestination(), '?');
        $destination = substr($response->getDestination(), 0, $pos);
        $queryString = substr($response->getDestination(), $pos+1);

        $this->assertEquals($this->destination, $destination);

        $bindingRequest = new Request();
        $data = $bindingRequest->parseQueryString($queryString, true);
        $this->checkData($data);


        /** @var AuthnRequest $authnRequest */
        $authnRequest = $binding->receive($bindingRequest);
        $this->assertTrue($authnRequest instanceof AuthnRequest);
        $this->checkRequest($authnRequest, $id, $time);
    }





    private function checkData(array $data) {
        $this->assertTrue(array_key_exists('SAMLRequest', $data));
        $this->assertTrue(array_key_exists('RelayState', $data));
        $this->assertTrue(array_key_exists('SigAlg', $data));
        $this->assertTrue(array_key_exists('Signature', $data));

        $this->assertEquals($this->relayState, $data['RelayState']);
        $this->assertEquals($this->sigAlg, $data['SigAlg']);
    }

}