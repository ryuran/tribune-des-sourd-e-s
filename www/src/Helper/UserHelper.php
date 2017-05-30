<?php

namespace App\Helper;

use Symfony\Component\HttpFoundation\Request;

abstract class UserHelper
{
    public static function randomPassword(int $length = 5): string
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        return substr(str_shuffle($chars), 0, $length);
    }

    public function getUserHash(Request $request): string
    {
        return md5($request->getClientIp().$request->headers->get('User-Agent').$request->getSession()->getId());
    }
}
