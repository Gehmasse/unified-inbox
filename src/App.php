<?php

namespace Micro\UnifiedInbox;

use RuntimeException;

class App
{
    public static function env(): Env
    {
        return new Env(require __DIR__ . '/../env.php');
    }

    public static function imap(?string $account): IMAP
    {
        $account = self::env()->accounts()[$account] ?? null;

        if (!$account) {
            throw new RuntimeException('Unknown account "' . $account . '"');
        }

        return new IMAP($account);
    }

    public static function controller(): Controller
    {
        return new Controller();
    }

    public static function router(): Router
    {
        return new Router($_GET['page'] ?? '');
    }

    public function response(): string
    {
        return self::router()->response();
    }
}