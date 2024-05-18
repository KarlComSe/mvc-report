<?php

namespace App\Model;

use Exception;

class CardCollection
{
    /**
     * @var array<Card> The Cards in the card collection.
     */
    private array $cards = [];

    public function addCard(Card $card): void
    {
        $this->cards[] = $card;
    }

    /**
     * Draw some cards from the card collection.
     *
     * @return array<Card> The array representing containing drawn cards.
     */
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

    /**
     * Get the deck of cards.
     *
     * @return array<Card> The array representing containing drawn cards.
     */
    public function getCards(): array
    {
        return $this->cards;
    }
    /**
     * Set the deck of cards.
     * 
     * @param array<Card> $cards The array of cards to set.
     */
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
