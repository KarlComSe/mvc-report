<?php

namespace App\Model;

use App\Model\CardCollection;
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
     * @var CardCollection The cards in the deck.
     **/
    public CardCollection $cardCollection;

    protected SplObjectStorage $observers;

    protected bool $isShuffled = false;

    /**
     * Constructor for the DeckOfCards class.
     *
     * @param array $observers An optional array of observer objects to attach to the deck.
     */
    public function __construct(array $observers = [])
    {
        $this->cardCollection = new CardCollection();
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
        $this->cardCollection->addCard($card);
        $this->notify();
    }

    /**
     * Get the deck of cards.
     *
     * @return array<Card> The array representing the deck of cards.
     */
    public function getDeck(): array
    {
        return $this->cardCollection->getCards();
    }

    public function setDeck(array $cards): void
    {
        $this->cardCollection->setCards($cards);
        $this->notify();
    }

    public function hasCards(): bool
    {
        return $this->cardCollection->hasCards();
    }

    /**
     * Draws a specified number of cards from the deck.
     *
     * @param int $numberOfCards The number of cards to draw.
     * @return array<Card> An array of drawn cards.
     */
    public function drawCards(int $numberOfCards): array
    {
        $drawnCards = $this->cardCollection->drawCards($numberOfCards);
        $this->notify();
        return $drawnCards;
    }
}
