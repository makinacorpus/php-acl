<?php

class BitmaskingTest extends \PHPUnit_Framework_TestCase
{
    public function testHashCollision()
    {
        $words = explode("\n", file_get_contents(__DIR__ . '/words.txt'));
        $map = [];

        foreach ($words as $word) {
            $map[crc32($word) % 32][] = $word;
        }

        usort($map, function ($a, $b) {
            return count($a) - count($b);
        });

        print_r($map);
    }
}
