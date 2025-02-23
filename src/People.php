<?php

namespace Micro\UnifiedInbox;

readonly class People
{
    public function __construct(private array $people)
    {
    }

    private function people(): array
    {
        return array_map(fn(object $person) => new Person($person), $this->people);
    }

    public function toArray()
    {
        return array_map(fn(Person $person) => $person->toArray(), $this->people());
    }
}