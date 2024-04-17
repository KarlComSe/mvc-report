<?php

namespace App\Model;

use App\Model\Card;

class CardHand
{
    private ?int $maxNumberOfCardsInHand;
    public array $cardsInHand = [];

    public function __construct(?int $maxNumberOfCardsInHand = null)
    {
        $this->maxNumberOfCardsInHand = $maxNumberOfCardsInHand;
    }

    public function addCard(Card $card)
    {
        if ($this->maxNumberOfCardsInHand === null) {
            $this->cardsInHand[] = $card;
            return;
        }

        if ($this->maxNumberOfCardsInHand > len($this->cardsInHand)) {
            $this->cardsInHand[] = $card;
        }
    }

    public function getHand(): array
    {
        return $this->cardsInHand;
    }

    public function getScore(): array
    {
        $scoreArray = [];

        foreach ($this->cardsInHand as $card) {
            $currentValues = [];
            if ($card->getAlternativeValue() !== null) {
                $currentValues[] = $card->getAlternativeValue();
            }
            $currentValues[] = $card->getValue();

            if (count($scoreArray) === 0) {
                $scoreArray = $currentValues;
                continue;
            }

            $newScoreArray = [];
            foreach ($scoreArray as $score) {
                foreach ($currentValues as $value) {
                    $newScoreArray[] = $score + $value;
                }
            }
            $scoreArray = $newScoreArray;
        }
    }
}
