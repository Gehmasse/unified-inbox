<?php

namespace Micro\UnifiedInbox;

readonly class Person
{
    public function __construct(private object $person)
    {
    }

    public function address(): string
    {
        return $this->person->mailbox . '@' . $this->person->host;
    }

    public function name(): string
    {
        if (!@$this->person->personal) {
            return '---';
        }

        return Parser::headerDecode($this->person->personal);
    }

    public function long(): string
    {
        $name = $this->name();

        if ($name === '---') {
            return $this->address();
        }

        return $name . ' <' . $this->address() . '>';
    }

    public function short(): string
    {
        $name = $this->name();

        if ($name === '---') {
            return $this->address();
        }

        return $name;
    }

    public function toArray(): array
    {
        return [
            'address' => $this->address(),
            'name' => $this->name(),
            'long' => $this->long(),
            'short' => $this->short(),
        ];
    }
}