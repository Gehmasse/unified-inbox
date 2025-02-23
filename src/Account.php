<?php

namespace Micro\UnifiedInbox;

readonly class Account
{
    public function __construct(
        public string $key,
        public string $title,
        public string $host,
        public string $username,
        public string $password,
        public int    $port = 993,
        public bool   $ssl = true,
    )
    {
    }
}