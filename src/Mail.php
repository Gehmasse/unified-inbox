<?php

namespace Micro\UnifiedInbox;

readonly class Mail
{
    public function __construct(private int $id, private object $header)
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

        $decoded = '';

        foreach (imap_mime_header_decode($this->header->subject) as $part) {
            $decoded .= mb_convert_encoding($part->text, 'UTF-8', $part->charset === 'default' ? 'US-ASCII' : $part->charset);
        }

        return $decoded;
    }

    public function from(): string
    {
        return $this->header->from[0]->mailbox . '@' . $this->header->from[0]->host;
    }

    public function to(): string
    {
        return $this->header->to[0]->mailbox . '@' . $this->header->to[0]->host;
    }

    public function date(): string
    {
        return $this->header->date;
    }

    // TODO: from_long, to_long

    public function toArray(): array
    {
        return [
            'id' => $this->id(),
            'subject' => $this->subject(),
            'from' => $this->from(),
            'to' => $this->to(),
            'date' => $this->date(),
            'links' => [
                'mail' => '/?page=mail&id=' . $this->id(),
                'body' => '/?page=body&id=' . $this->id(),
            ],
        ];
    }
}