<?php

namespace App\Model;

class HumanPlayer extends Player
{
    public function __construct(string $name = 'human', int $balance = 1000)
    {
        parent::__construct($name, $balance);
    }
}
