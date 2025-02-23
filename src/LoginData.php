<?php

namespace Micro\UnifiedInbox;

readonly class LoginData
{
    public function __construct(
        public string $host,
        public string $username,
        public string $password,
        public string $folder = "INBOX",
        public int    $port = 993,
        public bool   $ssl = true,
    )
    {
    }
}