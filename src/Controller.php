<?php

namespace Micro\UnifiedInbox;

use RuntimeException;

class Controller
{

    public function folders(): string
    {
        $folders = [];

        foreach (App::env()->accounts() as $account) {
            $folders = [...$folders, ...new IMAP($account)->folders(prefix: $account->title)];
        }

        return Response::json($folders);
    }

    public function mails(): string
    {
        $mails = [];

        foreach (App::env()->accounts() as $account) {
            $mails = [...$mails, ...new IMAP($account)->mails(limit: $_GET['limit'] ?? 20)];
        }

        usort($mails, fn(Mail $a, Mail $b) => $b->date() <=> $a->date());

        return Response::json(array_map(fn(Mail $mail) => $mail->toArray(), $mails));
    }

    public function mail(): string
    {
        return Response::json(App::imap(@$_GET['account'])->mail(@$_GET['id'])?->toArray());
    }

    public function body(): string
    {
        [$body, $type] = App::imap(@$_GET['account'])->body(@$_GET['id']);

        if(!$type) {
            return Response::json(null);
        }

        if ($type === 'plain') {
            return Response::plain($body);
        }

        if ($type === 'html') {
            return Response::html($body);
        }

        throw new RuntimeException('unknown body type: ' . json_encode($type));
    }

    public function playground(): mixed
    {
        dd(App::env()->accounts());
    }

}