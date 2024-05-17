<?php

namespace App\Model;

use Exception;

class CardCollection
{
    private array $cards = [];

    public function addCard(Card $card): void
    {
        $this->cards[] = $card;
    }

    public function drawCards(int $numberOfCards): array
    {
        $drawnCards = [];
        for ($i = 0; $i < $numberOfCards; $i++) {
            if (empty($this->cards)) {
                throw new Exception('No cards left in the deck');
            }
            $drawnCards[] = array_pop($this->cards);
        }
        return $drawnCards;
    }

    public function getCards(): array
    {
        return $this->cards;
    }

    public function setCards(array $cards): void
    {
        $this->cards = $cards;
    }

    public function hasCards(): bool
    {
        return !empty($this->cards);
    }

    public function getNumberOfCards(): int
    {
        return count($this->cards);
    }
}
