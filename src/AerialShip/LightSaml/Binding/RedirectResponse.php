<?php

namespace AerialShip\LightSaml\Binding;


class RedirectResponse extends Response
{
    /** @var  string */
    protected $url;


    /**
     * @param string $url
     */
    function __construct($url) {
        $this->url = $url;
    }


    /**
     * @param string $url
     */
    public function setUrl($url) {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getUrl() {
        return $this->url;
    }


    function render() {
        header('Location: ' . $this->getUrl(), true, 302);
        header('Pragma: no-cache');
        header('Cache-Control: no-cache, must-revalidate');
    }
}