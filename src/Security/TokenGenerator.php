<?php
namespace App\Security;

class TokenGenerator
{
    public const DEFAULT_SIZE = 128;
    public const DEFAULT_CHARSET = 'ABCDEFGHIJKLMNOPQRSTUWVXYZabcdefghijklmnopqrstuwvxyz0123456789';

    public function generateToken(int $size = self::DEFAULT_SIZE, string $charset = self::DEFAULT_CHARSET): string
    {
        $token = '';
        for ($index = 0; $index < $size; ++$index) {
            $token .= $charset[random_int(0, strlen($charset) - 1)];
        }

        return $token;
    }
};
