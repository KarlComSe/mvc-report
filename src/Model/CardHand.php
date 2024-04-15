<?php

namespace App\Model;

use App\Model\Card;

class CardHand{
    private ?int $maxNumberOfCardsInHand;
    private array $cardsInHand = [];

    public function __construct(?int $maxNumberOfCardsInHand = null){
        $this->maxNumberOfCardsInHand = $maxNumberOfCardsInHand;
    }

    public function addCard(Card $card){
        if ($this->maxNumberOfCardsInHand === null){
            $this->cardsInHand[] = $card;
            return;
        } 

        if ($this->maxNumberOfCardsInHand > len($this->cardsInHand)) {
            $this->cardsInHand[] = $card;
        }
    }

    public function getHand(): array{
        return $this->cardsInHand;
    }
}