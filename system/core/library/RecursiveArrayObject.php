<?php

/**
 * @author  iltar van der berg
 * @version 1.0.1
 */

class RecursiveArrayObject extends ArrayObject
{
    /**
     * overwrites the ArrayObject constructor for
     * iteration through the "array". When the item
     * is an array, it creates another self() instead
     * of an array
     *
     * @param Array $array data array
     */
    public function __construct(Array $array)
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $value = new self($value);
            }
            $this->offsetSet($key, $value);
        }
        $this->setFlags(ArrayObject::ARRAY_AS_PROPS);
    }

    /**
     * returns Array when printed (like "echo array();")
     * instead of an error
     *
     * @return UTF8String
     */
    public function __ToString()
    {
        return 'Array';
    }
}
