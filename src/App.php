<?php

namespace Micro\UnifiedInbox;

use RuntimeException;

class App
{
    public static function env(): Env
    {
        return new Env(require __DIR__ . '/../env.php');
    }

    public static function imap(): IMAP
    {
        $accounts = self::env()->accounts();

        if(count($accounts) > 0) {
            return new IMAP($accounts[0]);
        }

        throw new RuntimeException('No accounts found');
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