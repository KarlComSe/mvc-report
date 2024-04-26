<?php

namespace App\Model;

use App\Model\Card;


class CardHand
{
    public array $cardsInHand = [];
    public ?int $maxCardsInHand = null;

    public function __construct(?int $maxCardsInHand = null)
    {
        $this->maxCardsInHand = $maxCardsInHand;
    }

    public function addCard(Card $card): void
    {
        if ($this->maxCardsInHand === null) {
            $this->cardsInHand[] = $card;
            return;
        }

        if ($this->maxCardsInHand > count($this->cardsInHand)) {
            $this->cardsInHand[] = $card;
        }
    }

    /**
     * Get the cards in the hand.
     *
     * @return array<Card> The cards in the hand.
     */
    public function getHand(): array
    {
        return $this->cardsInHand;
    }

    /**
     * Get the score of the card hand.
     *
     * @return array<int> The array containing all possible scores of the card hand.
     */
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
        return $scoreArray;
    }
}
