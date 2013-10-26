<?php

namespace AerialShip\LightSaml\Model;


trait GetItemsByClassTrait
{

    /**
     * @param \object[] $items
     * @param string $class
     * @return \object[]
     */
    protected function getItemsByClass(array $items, $class) {
        $class = ltrim($class, '\\');
        $result = array();
        foreach ($items as $item) {
            $itemClass = get_class($item);
            if ($itemClass == $class) {
                $result[] = $item;
            } else {
                if (($pos = strrpos($itemClass, '\\')) !== false) {
                    $itemClass = substr($itemClass, $pos+1);
                }
                if ($itemClass == $class) {
                    $result[] = $item;
                }
            }
        }
        return $result;
    }

}