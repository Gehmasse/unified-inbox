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

        usort($mails, fn(Mail $a, Mail $b) => $a->seen() <=> $b->seen() ?: $b->date() <=> $a->date());

        return Response::json(array_map(fn(Mail $mail) => $mail->toArray(), $mails));
    }

    public function mail(): string
    {
        return Response::json(App::imap(@$_GET['account'])->mail(@$_GET['id'])?->toArray());
    }

    public function body(): string
    {
        [$body, $type] = App::imap(@$_GET['account'])->body(@$_GET['id']);

        if ($type === 'plain') {
            return Response::plain($body);
        }

        if ($type === 'html') {
            return Response::html($body);
        }

        return Response::json(null);
    }

    public function playground(): never
    {
        dd(App::env()->accounts());
    }

}