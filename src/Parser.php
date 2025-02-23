<?php

namespace Micro\UnifiedInbox;

class Parser
{

    public static function decode(string $string, int $type): string
    {
        return match ($type) {
            3 => base64_decode($string),
            4 => quoted_printable_decode($string),
            default => $string,
        };
    }

    public static function charset(array $parameters): ?string
    {
        foreach ($parameters as $param) {
            if ($param->attribute === 'charset') {
                return $param->value;
            }
        }

        return null;
    }

    public static function parts(object $structure, $path = ''): array
    {
        $parts = [];

        foreach ($structure->parts as $index => $part) {
            $part_number = $index + 1;

            $parts[$part->subtype] = [
                trim($path . '.' . $part_number, '.'),
                $part->encoding,
                self::charset($part->parameters),
            ];

            if (key_exists('parts', (array)$part)) {
                $parts = [...$parts, ...self::parts($part, $path . '.' . $part_number)];
            }
        }

        return $parts;
    }

    public static function multiPartContent(object $structure): array
    {
        $parts = Parser::parts($structure);

        if (key_exists('HTML', $parts)) {
            return [...$parts['HTML'], 'html'];
        }

        if (key_exists('PLAIN', $parts)) {
            return [...$parts['PLAIN'], 'plain'];
        }

        return [false, false, false, false];
    }

    public static function isMultiPart(object $structure): bool
    {
        return isset($structure->parts) && count($structure->parts);
    }

    public static function singlePartContent(object $structure): array
    {
        return [
            $structure->encoding,
            Parser::charset($structure->parameters),
            strtolower($structure->subtype),
        ];
    }
}