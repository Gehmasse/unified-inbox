<?php

namespace Micro\UnifiedInbox;

class App
{
    public static function env(): Env
    {
        return new Env(require __DIR__ . '/../env.php');
    }

    public static function imap(): IMAP
    {
        $login = new LoginData(
            host: self::env()->host(),
            username: self::env()->username(),
            password: self::env()->password(),
        );

        return new IMAP($login);
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