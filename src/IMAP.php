<?php

namespace Micro\UnifiedInbox;

use IMAP\Connection;
use RuntimeException;

class IMAP
{
    private Connection $connection;

    public function __construct(
        private readonly Account $auth,
    )
    {
        $this->connect();
    }

    private function connect(): void
    {
        $connection = imap_open($this->mailbox(), $this->auth->username, $this->auth->password);

        if (!($connection instanceof Connection)) {
            throw new RuntimeException("IMAP Connection failed: " . imap_last_error());
        }

        $this->connection = $connection;
    }

    private function protocol(): string
    {
        return $this->auth->ssl ? '/imap/ssl' : '/imap';
    }

    private function mailbox(string $folder = 'INBOX'): string
    {
        return '{' . $this->auth->host . ':' . $this->auth->port . $this->protocol() . '}' . $folder;
    }


    public function folders(string $folder = ""): array
    {
        $mailbox = $this->mailbox($folder);

        $folders = imap_list($this->connection, $mailbox, "*");

        if (!$folders) {
            return [];
        }

        return array_map(fn(string $folder) => str_replace($mailbox, '', $folder), $folders);
    }

    public function mails(string $folder = "INBOX", int $limit = 10): array
    {
        $mailbox = $this->mailbox($folder);

        $emails = imap_search($this->connection, 'ALL');

        if (!$emails) {
            return [];
        }

        rsort($emails);
        $emails = array_slice($emails, 0, $limit);

        $messages = [];

        foreach ($emails as $email) {
            $header = imap_headerinfo($this->connection, $email);

            $messages[] = new Mail(id: $email, header: $header);
        }

        return $messages;
    }

    public function __destruct()
    {
        imap_close($this->connection);
    }

    public function body(int $id): array
    {
        $structure = imap_fetchstructure($this->connection, $id);

        if (!$structure) {
            return [false, false];
        }

        if (Parser::isMultiPart($structure)) {
            [$part_number, $encoding, $charset, $type] = Parser::multiPartContent($structure);

            $body = $part_number
                ? imap_fetchbody($this->connection, $id, $part_number)
                : false;
        } else {
            [$encoding, $charset, $type] = Parser::singlePartContent($structure);

            $body = imap_body($this->connection, $id);
        }

        $result = Parser::decode($body, $encoding);

        if (!$body || trim($body) == '') {
            throw new RuntimeException("Body cannot be empty");
        }

        if ($encoding) {
            $result = mb_convert_encoding($result, 'utf-8', $charset);
        }

        return [$result, $type];
    }

    public function mail(int $id): array
    {
        $header = imap_headerinfo($this->connection, $id);

        return new Mail(id: $id, header: $header)->toArray();
    }
}