<?php

namespace App\Model;

class BankPlayer extends Player implements AiPlayerInterface
{
    public function __construct(string $name = 'bank', int $balance = 3000)
    {
        parent::__construct($name, $balance);
    }

    public function makeMove()
    {
        $scores = $this->getScores();
        $maxScore = 0;
        foreach ($scores as $score) {
            if ($score <= 21 && $score > $maxScore) {
                $maxScore = $score;
            }
        }
        if ($maxScore < 17) {
            return 'hit';
        }
        return 'stand';
    }
}
