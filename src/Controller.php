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
            $mails = [...$mails, ...new IMAP($account)->mails(limit: $_REQUEST['limit'] ?? 20)];
        }

        usort($mails, fn(Mail $a, Mail $b) => $a->seen() <=> $b->seen() ?: $b->date() <=> $a->date());

        return Response::json(array_map(fn(Mail $mail) => $mail->toArray(), $mails));
    }

    public function mail(): string
    {
        return Response::json(App::imap(@$_REQUEST['account'])->mail(@$_REQUEST['id'])?->toArray());
    }

    public function body(): string
    {
        [$body, $type] = App::imap(@$_REQUEST['account'])->body(@$_REQUEST['id']);

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
        dd();
    }

    public function register(): string
    {
        if (!App::auth()->verifyPassword()) {
            return Response::json(['status' => false]);
        }

        return Response::json(['status' => true, 'token' => App::auth()->register()]);
    }

    public function login(): string
    {
        $status = isset($_REQUEST['token']) && App::auth()->verifyToken($_REQUEST['token']);

        return Response::json(['status' => $status]);
    }

}