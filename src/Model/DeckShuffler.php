<?php

namespace App\Model;

use Random\Randomizer;

class DeckShuffler
{
    private Randomizer $randomizer;

    public function __construct(Randomizer $randomizer)
    {
        $this->randomizer = $randomizer;
    }

    public function shuffle(DeckOfCards $deck): void
    {
        $cards = $deck->getDeck();
        $shuffledCards = $this->randomizer->shuffleArray($cards);
        $deck->setDeck($shuffledCards);
    }
}
