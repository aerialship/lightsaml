<?php

namespace AerialShip\LightSaml\Binding;


class PostResponse
{
    /** @var  string */
    protected $destination;

    /** @var  array */
    protected $data;


    /**
     * @param string $destination
     * @param array $data
     */
    function __construct($destination, array $data = array()) {
        $this->destination = $destination;
        $this->data = $data;
    }


    /**
     * @param array $data
     */
    public function setData($data) {
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function getData() {
        return $this->data;
    }

    /**
     * @param string $destination
     */
    public function setDestination($destination) {
        $this->destination = $destination;
    }

    /**
     * @return string
     */
    public function getDestination() {
        return $this->destination;
    }




    /**
     * @return string
     */
    function render() {
        $template = new HttpPostTemplate($this->getDestination(), $this->getData());
        ob_start();
        $template->render();
        $result = ob_get_clean();
        return $result;
    }

} 