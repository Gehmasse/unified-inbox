<?php

namespace Micro\UnifiedInbox;

use IMAP\Connection;
use RuntimeException;
use Throwable;

class IMAP
{
    private Connection $connection;

    public function __construct(
        private readonly Account $account,
    )
    {
        $this->connect();
    }

    private function connect(): void
    {
        $connection = imap_open($this->mailbox(), $this->account->username, $this->account->password);

        if (!($connection instanceof Connection)) {
            throw new RuntimeException("IMAP Connection failed: " . imap_last_error());
        }

        $this->connection = $connection;
    }

    private function protocol(): string
    {
        return $this->account->ssl ? '/imap/ssl' : '/imap';
    }

    private function mailbox(string $folder = 'INBOX'): string
    {
        return '{' . $this->account->host . ':' . $this->account->port . $this->protocol() . '}' . $folder;
    }

    public function folders(string $folder = '', string $prefix = ''): array
    {
        $mailbox = $this->mailbox($folder);

        $folders = imap_list($this->connection, $mailbox, '*');

        if (!$folders) {
            return [];
        }

        return array_map(function (string $folder) use ($mailbox, $prefix) {
            $name = str_replace($mailbox, '', $folder);

            return $prefix ? $prefix . '/' . $name : $name;
        }, $folders);
    }

    public function mails(int $limit = 10): array
    {
        $emails = imap_search($this->connection, 'ALL');

        if (!$emails) {
            return [];
        }

        rsort($emails);
        $emails = array_slice($emails, 0, $limit);

        $messages = [];

        foreach ($emails as $email) {
            $header = imap_headerinfo($this->connection, $email);

            $messages[] = new Mail(id: $email, header: $header, account: $this->account);
        }

        return $messages;
    }

    public function __destruct()
    {
        imap_close($this->connection);
    }

    public function body(int $id): array
    {
        $structure = @imap_fetchstructure($this->connection, $id);

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

    public function mail(int $id): ?Mail
    {
        $header = @imap_headerinfo($this->connection, $id);

        if (!$header) {
            return null;
        }

        return new Mail(id: $id, header: $header, account: $this->account);
    }
}