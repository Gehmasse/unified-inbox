<?php

namespace Micro\UnifiedInbox;

readonly class Mail
{
    public function __construct(private int $id, private object $header, private Account $account)
    {
    }

    public function id(): int
    {
        return $this->id;
    }

    public function subject(): string
    {
        if (!$this->header->subject) {
            return '---';
        }

        return Parser::headerDecode($this->header->subject);
    }

    public function from(): People
    {
        return new People($this->header->from);
    }

    public function to(): People
    {
        return new People($this->header->to);
    }

    public function date(): string
    {
        return date('Y-m-d H:i:s', strtotime($this->header->date));
    }

    // TODO: from_long, to_long

    public function toArray(): array
    {
        return [
            'id' => $this->id(),
            'subject' => $this->subject(),
            'from' => $this->from()->toArray(),
            'to' => $this->to()->toArray(),
            'date' => $this->date(),
            'links' => [
                'mail' => '/?page=mail&id=' . $this->id(),
                'body' => '/?page=body&id=' . $this->id(),
            ],
            'account' => $this->account->key,
        ];
    }
}