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
        return new Router($_REQUEST['page'] ?? '');
    }

    public static function response(): string
    {
        return self::router()->response();
    }

    public static function db(): DB
    {
        return new DB();
    }

    public static function run(): string
    {
        if (isset($_REQUEST['password'])) {
            return self::controller()->login();
        }

        if (!self::auth()->verifyToken(@$_REQUEST['token'] ?? '')) {
            http_response_code(401);

            return 'Unauthorized';
        }

        return self::response();
    }

    public static function auth(): Auth
    {
        return new Auth();
    }
}