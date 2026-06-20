<?php

class ContentService
{
    public static function load($path)
    {
        $raw = file_get_contents($path);

        // Split front matter
        preg_match('/---(.*?)---(.*)/s', $raw, $matches);

        $yaml = trim($matches[1]);
        $markdown = trim($matches[2]);

        $data = self::parseYaml($yaml);

        $html = Parsedown::instance()->text($markdown);

        return [
            'data' => $data,
            'content' => $html
        ];
    }

    private static function parseYaml($yaml)
    {
        $lines = explode("\n", $yaml);
        $result = [];

        foreach ($lines as $line) {
            if (strpos($line, ':') !== false) {
                [$key, $value] = explode(':', $line, 2);
                $result[trim($key)] = trim($value, " \"");
            }
        }

        return $result;
    }
}