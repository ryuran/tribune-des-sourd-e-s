<?php

namespace App\Interfaces;

interface CoreEntityInterface
{
    public function toArray(int $callerUserId = null): array;
}
