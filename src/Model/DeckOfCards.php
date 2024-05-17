<?php

namespace App\Model;

use SplSubject;
use SplObjectStorage;
use SplObserver;
use Exception;

abstract class DeckOfCards implements SplSubject
{
    /**
     * @var array<string> The suits of the cards in a deck.
     */
    public static $suitsOfCards = ['Spade', 'Diamond', 'Heart', 'Club'];

    public static $namesOfCards = [
        'Ace' => [1, 14],
        '2' => [2],
        '3' => [3],
        '4' => [4],
        '5' => [5],
        '6' => [6],
        '7' => [7],
        '8' => [8],
        '9' => [9],
        '10' => [10],
        'Jack' => [11],
        'Queen' => [12],
        'King' => [13],
    ];
    /**
     * @var array<Card> The array of cards in the deck.
     **/
    public array $cards = [];

    protected SplObjectStorage $observers;

    protected bool $isShuffled = false;

    /**
     * Constructor for the DeckOfCards class.
     *
     * @param array $observers An optional array of observer objects to attach to the deck.
     */
    public function __construct(array $observers = [])
    {
        $this->observers = new SplObjectStorage();
        foreach ($observers as $observer) {
            $this->attach($observer);
        }
    }

    public function getObservers(): SplObjectStorage
    {
        return $this->observers;
    }

    public function attach(SplObserver $observer): void
    {
        $this->observers->attach($observer);
    }
    public function detach(SplObserver $observer): void
    {
        $this->observers->detach($observer);
    }
    public function notify(): void
    {
        foreach ($this->observers as $observer) {
            $observer->update($this->getDeck());
        }
    }

    abstract public static function create(array $observers = []): DeckOfCards;

    abstract public function sort(): void;

    public function addCard(Card $card): void
    {
        $this->cards[] = $card;
        $this->notify();
    }

    /**
     * Get the deck of cards.
     *
     * @return array<Card> The array representing the deck of cards.
     */
    public function getDeck(): array
    {
        return $this->cards;
    }

    public function setDeck(array $cards): void
    {
        $this->cards = $cards;
        $this->notify();
    }

    public function hasCards(): bool
    {
        return count($this->cards) > 0;
    }

    public function getNumberOfCards(): int
    {
        return count($this->cards);
    }

    /**
     * Draws a specified number of cards from the deck.
     *
     * @param int $numberOfCards The number of cards to draw.
     * @return array<Card> An array of drawn cards.
     */
    public function drawCards(int $numberOfCards): array
    {
        if (!$this->hasCards()) {
            throw new Exception('No cards left in the deck');
        }
        $cards = [];
        for ($i = 0; $i < $numberOfCards; $i++) {
            $cards[] = array_pop($this->cards);
            $this->notify();
        }
        return $cards;
    }

    /**
     * Deals a specified number of cards to a specified number of players.
     *
     * @param int $numberOfPlayers The number of players to deal cards to.
     * @param int $numberOfCards The number of cards to deal to each player.
     * @return array<CardHand> An array of CardHand objects representing the hands of each player.
     */
    public function dealCards(int $numberOfPlayers, int $numberOfCards): array
    {
        $hands = [];
        for ($i = 0; $i < $numberOfPlayers; $i++) {
            $hand = new CardHand();
            for ($j = 0; $j < $numberOfCards; $j++) {
                $card = $this->drawCards(1)[0];
                $hand->addCard($card);
            }
            $hands[] = $hand;
        }

        $this->notify();
        return $hands;
    }
}
