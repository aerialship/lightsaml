<?php

namespace AerialShip\LightSaml\Binding;


use AerialShip\LightSaml\Error\BindingException;
use AerialShip\LightSaml\Meta\SerializationContext;
use AerialShip\LightSaml\Model\Protocol\AbstractRequest;
use AerialShip\LightSaml\Model\Protocol\Message;

class HttpPost extends AbstractBinding
{

    /**
     * @param Message $message
     * @return PostResponse
     */
    function send(Message $message) {
        $destination = $message->getDestination() ?: $this->getDestination();

        $context = new SerializationContext();
        $message->getSignedXml($context->getDocument(), $context);
        $msgStr = $context->getDocument()->saveXML();

        $this->dispatchSend($msgStr);

        $msgStr = base64_encode($msgStr);

        $type = $message instanceof AbstractRequest ? 'SAMLRequest' : 'SAMLResponse';

        $data = array($type => $msgStr);
        if ($message->getRelayState()) {
            $data['RelayState'] = $message->getRelayState();
        }

        $result = new PostResponse($destination, $data);
        return $result;
    }


    /**
     * @param Request $request
     * @return Message
     * @throws \AerialShip\LightSaml\Error\BindingException
     */
    function receive(Request $request) {
        $post = $request->getPost();
        if (array_key_exists('SAMLRequest', $post)) {
            $msg = $post['SAMLRequest'];
        } elseif (array_key_exists('SAMLResponse', $post)) {
            $msg = $post['SAMLResponse'];
        } else {
            throw new BindingException('Missing SAMLRequest or SAMLResponse parameter');
        }

        $msg = base64_decode($msg);

        $this->dispatchReceive($msg);

        $doc = new \DOMDocument();
        $doc->loadXML($msg);
        $result = Message::fromXML($doc->firstChild);

        if (array_key_exists('RelayState', $post)) {
            $result->setRelayState($post['RelayState']);
        }

        return $result;
    }


}
