<?php

namespace AerialShip\LightSaml\Model;


use AerialShip\LightSaml\Helper;

trait GetItemsByClassTrait
{

    /**
     * @param \object[] $items
     * @param string $class
     * @return \object[]
     */
    protected function getItemsByClass(array $items, $class) {
        $result = array();
        foreach ($items as $item) {
            if (Helper::doClassNameMatch($item, $class)) {
                $result[] = $item;
            }
        }
        return $result;
    }

}