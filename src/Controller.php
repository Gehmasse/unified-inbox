<?php

namespace Micro\UnifiedInbox;

use RuntimeException;

class Controller
{

    public function folders(): string
    {
        return Response::json(App::imap()->folders());
    }

    public function mails(): string
    {
        return Response::json(array_map(fn(Mail $mail) => $mail->toArray(), App::imap()->mails(limit: $_GET['limit'] ?? 50)));
    }

    public function mail(): string
    {
        return Response::json(App::imap()->mail($_GET['id']));
    }

    public function body(): string
    {
        [$body, $type] = App::imap()->body($_GET['id']);

        if ($type === 'plain') {
            return Response::plain($body);
        }

        if ($type === 'html') {
            return Response::html($body);
        }

        throw new RuntimeException('unknown body type: ' . json_encode($type));
    }

}