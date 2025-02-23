<?php

namespace Micro\UnifiedInbox;

readonly class Router
{

    public function __construct(private string $page)
    {
    }

    public function response(): string
    {
        $controller = App::controller();

        return match ($this->page) {
            'folders' => $controller->folders(),
            'mails' => $controller->mails(),
            'mail' => $controller->mail(),
            'body' => $controller->body(),
            default => 'Not Found: "' . $this->page . '"',
        };
    }
}