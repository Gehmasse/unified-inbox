<?php

namespace Micro\UnifiedInbox;

class Auth
{

    public function login(): string
    {
        $token = $this->generateToken();

        App::db()->insert('sessions', [
            'token' => $token,
            'expiry' => time() + 60 * 60, // expires after one hour
        ]);

        return $token;
    }

    public function verifyToken(): bool
    {
        $token = @$_REQUEST['token'] ?? '';

        $record = App::db()->find('sessions', 'token', $token);

        return $record && @$record->expiry > time();
    }

    public function verifyPassword(): bool
    {
        return password_verify(@$_REQUEST['password'] ?? '', App::env()->passwordHash());
    }

    private function generateToken(): string
    {
        return bin2hex(random_bytes(64));
    }
}