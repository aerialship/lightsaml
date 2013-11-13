<?php

namespace AerialShip\LightSaml\Tests\Binding;

use AerialShip\LightSaml\Binding\HttpPost;
use AerialShip\LightSaml\Binding\PostResponse;
use AerialShip\LightSaml\Binding\Request;
use AerialShip\LightSaml\Model\Protocol\AuthnRequest;


class HttpPostTest extends Base
{
    function testAuthnRequest() {
        $authnRequest = $this->getRequest();
        $id = $authnRequest->getID();
        $time = $authnRequest->getIssueInstant();

        $binding = new HttpPost();

        /** @var PostResponse $response */
        $response = $binding->send($authnRequest);
        $this->assertNotNull($response);
        $this->assertTrue($response instanceof PostResponse);
        $this->assertEquals($this->destination, $response->getDestination());

        /** @var $authnRequest AuthnRequest */
        $bindingRequest = new Request();
        $bindingRequest->setPost($response->getData());
        $authnRequest = $binding->receive($bindingRequest);
        $this->assertTrue($authnRequest instanceof AuthnRequest);
        $this->checkRequest($authnRequest, $id, $time);
    }

}
