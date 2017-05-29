<?php

namespace App\Traits;

trait ArrayAccess
{
    public function toArray(int $callerUserId = null, array $hydration = []): array
    {
        $array = [];

        if ($callerUserId) {
            if (isset($hydration)) {
                ;
            }
        }

        return $array;
    }

    public function offsetSet($offset, $value)
    {
        $method = 'set' . ucfirst($offset);
        $this->$method($value);
    }

    public function offsetExists($offset)
    {
        return isset($this->$offset);
    }

    public function offsetUnset($offset)
    {
        $method = 'set' . ucfirst($offset);
        $this->$method(null);
    }

    public function offsetGet($offset)
    {
        $method = 'get' . ucfirst($offset);
        return isset($this->$offset) ? $this->$method() : null;
    }
}
