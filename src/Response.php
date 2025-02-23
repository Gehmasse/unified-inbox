<?php

namespace Micro\UnifiedInbox;

class Response
{

    public static function json(mixed $data): string
    {
        header('Content-Type: application/json; charset=utf-8');

        return json_encode($data);
    }

    public static function plain(string $data): string
    {
        header('Content-Type: text/plain; charset=utf-8');

        return $data;
    }

    public static function html(mixed $data): string
    {
        header('Content-Type: text/html; charset=utf-8');

        return $data;
    }
}