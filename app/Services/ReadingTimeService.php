<?php

namespace App\Services;

class ReadingTimeService
{
    public function calculate(string $text, int $wordsPerMinute = 200): int
    {
        if (empty(trim($text))) {
            return 0;
        }

        $cleanText = strip_tags($text);
        $words = preg_split('/\s+/u', $cleanText, -1, PREG_SPLIT_NO_EMPTY);
        $wordCount = count($words);

        return max(1, (int) ceil($wordCount / $wordsPerMinute));
    }
}
