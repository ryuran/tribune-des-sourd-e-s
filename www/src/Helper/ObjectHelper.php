<?php

namespace App\Helper;

abstract class ObjectHelper
{
    public static function getClassName($entity): string
    {
        $namespace = get_class($entity);
        $namespaces = explode('\\', $namespace);
        $className = end($namespaces);
        return strtolower($className);
    }
}
