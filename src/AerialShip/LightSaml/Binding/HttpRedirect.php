<?php

namespace AerialShip\LightSaml\Binding;

use AerialShip\LightSaml\Model\Protocol\AbstractRequest;
use AerialShip\LightSaml\Model\Protocol\Message;
use AerialShip\LightSaml\Model\XmlDSig\SignatureCreator;
use AerialShip\LightSaml\Protocol;


class HttpRedirect extends AbstractBinding
{


    /**
     * @param Message $message
     * @return void
     */
    function send(Message $message) {
        $url = $this->getRedirectURL($message);
        header('Location: ' . $url, true, 302);
        header('Pragma: no-cache');
        header('Cache-Control: no-cache, must-revalidate');
        exit;
    }

    /**
     * @throws \RuntimeException
     * @return Message
     */
    function receive() {
        $data = $this->parseQuery();

        if (array_key_exists('SAMLRequest', $data)) {
            $msg = $data['SAMLRequest'];
        } elseif (array_key_exists('SAMLResponse', $data)) {
            $msg = $data['SAMLResponse'];
        } else {
            throw new \RuntimeException('Missing SAMLRequest or SAMLResponse parameter');
        }

        if (array_key_exists('SAMLEncoding', $data)) {
            $encoding = $data['SAMLEncoding'];
        } else {
            $encoding = Protocol::ENCODING_DEFLATE;
        }

        $msg = base64_decode($msg);
        switch ($encoding) {
            case Protocol::ENCODING_DEFLATE:
                $msg = gzinflate($msg);
                break;
            default:
                throw new \RuntimeException("Unknown encoding: $encoding");
        }

        $doc = new \DOMDocument();
        $doc->loadXML($msg);

        $message = Message::fromXML($doc->firstChild);

        if (array_key_exists('RelayState', $data)) {
            $message->setRelayState($data['RelayState']);
        }

//        if (array_key_exists('Signature', $data)) {
//            if (!array_key_exists('SigAlg', $data)) {
//                throw new Exception('Missing signature algorithm.');
//            }
//
//            $signData = array(
//                'Signature' => $data['Signature'],
//                'SigAlg' => $data['SigAlg'],
//                'Query' => $data['SignedQuery'],
//            );
//            $msg->addValidator(array(get_class($this), 'validateSignature'), $signData);
//        }

        return $message;
    }


    /**
     * @param Message $message
     * @return string
     * @throws \RuntimeException
     */
    function getRedirectURL(Message $message) {
        $destination = $message->getDestination();
        $relayState = $message->getRelayState();
        $signature = $message->getSignature();
        if ($signature && !$signature instanceof SignatureCreator) {
            throw new \RuntimeException('Signature must be SignatureCreator');
        }
        /** @var $key \XMLSecurityKey */
        $key = $signature ? $signature->getXmlSecurityKey() : null;

        $doc = new \DOMDocument();
        $message->getXml($doc);
        $xml = $doc->saveXML();
        $xml = gzdeflate($xml);
        $xml = base64_encode($xml);

        if ($message instanceof AbstractRequest) {
            $msg = 'SAMLRequest=';
        } else {
            $msg = 'SAMLResponse=';
        }
        $msg .= urlencode($xml);

        if ($relayState !== null) {
            $msg .= '&RelayState=' . urlencode($relayState);
        }

        if ($key !== NULL) {
            $msg .= '&SigAlg=' . urlencode($key->type);
            $signature = $key->signData($msg);
            $msg .= '&Signature=' . urlencode(base64_encode($signature));
        }

        if (strpos($destination, '?') === FALSE) {
            $destination .= '?' . $msg;
        } else {
            $destination .= '&' . $msg;
        }

        return $destination;
    }


    /**
     * @return array
     */
    private function parseQuery() {
        /*
         * Parse the query string. We need to do this ourself, so that we get access
         * to the raw (urlencoded) values. This is required because different software
         * can urlencode to different values.
         */
        $data = array();
        $relayState = '';
        $sigAlg = '';
        $sigQuery = '';
        foreach (explode('&', $_SERVER['QUERY_STRING']) as $e) {
            $tmp = explode('=', $e, 2);
            $name = $tmp[0];
            if (count($tmp) === 2) {
                $value = $tmp[1];
            } else {
                /* No value for this paramter. */
                $value = '';
            }
            $name = urldecode($name);
            $data[$name] = urldecode($value);

            switch ($name) {
                case 'SAMLRequest':
                case 'SAMLResponse':
                    $sigQuery = $name . '=' . $value;
                    break;
                case 'RelayState':
                    $relayState = '&RelayState=' . $value;
                    break;
                case 'SigAlg':
                    $sigAlg = '&SigAlg=' . $value;
                    break;
            }
        }

        $data['SignedQuery'] = $sigQuery . $relayState . $sigAlg;

        return $data;
    }

} 