<?php

namespace Micro\UnifiedInbox;

use PDO;

class DB
{
    private const string DB_PATH = __DIR__ . '/../database.json';
    private array $tables = ['sessions' => ['key', 'expiry']];

    public function all(string $table): array
    {
        return @$this->get()->$table ?? [];
    }

    public function find(string $table, string $column, string $value): ?object
    {
        return array_find($this->all($table), fn($row) => @$row->$column === $value);
    }

    public function insert(string $table, array $insert): void
    {
        $data = $this->get();

        if (!@$data->$table) {
            $data->$table = [];
        }

        $data->$table[] = $insert;

        $this->set($data);
    }

    private function get(): object
    {
        $json = file_get_contents(self::DB_PATH);

        if (!$json) {
            return (object)[];
        }

        $data = json_decode($json);

        if (!$data) {
            return (object)[];
        }

        return $data;
    }

    private function set(object $data): void
    {
        file_put_contents(self::DB_PATH, json_encode($data, JSON_PRETTY_PRINT));
    }
}