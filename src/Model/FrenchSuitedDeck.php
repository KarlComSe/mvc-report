<?php

namespace App\Model;

use App\Model\DeckOfCards;
use Random\Randomizer;
use App\Model\Card;

class FrenchSuitedDeck extends DeckOfCards
{
    /**
     * Creates a new instance of FrenchSuitedDeck.
     *
     * @param Randomizer $randomizer The randomizer object to be used for shuffling the deck.
     * @param array<SplObserver> $observers [Optional] An array of SplObserver objects to be attached to the deck for notification on deck creation. Defaults to an empty array.
     * @return FrenchSuitedDeck The newly created FrenchSuitedDeck object.
     */
    public static function create(Randomizer $randomizer, array $observers = []): FrenchSuitedDeck
    {
        $deck = new FrenchSuitedDeck($randomizer, $observers);
        foreach (self::$suitsOfCards as $suit) {
            foreach (self::$namesOfCards as $name => $value) {
                $deck->addCard(new Card($suit, $name, ...$value));
            }
        }
        $deck->notify();
        return $deck;
    }

    /**
     * Create a FrenchSuitedDeck object from a session array.
     *
     * @param array<card> $deck The array containing the cards from the session.
     * @param Randomizer $randomizer The randomizer object used to shuffle the deck.
     * @param array<SplObserver> $observers The array of observer objects.
     * @return FrenchSuitedDeck The created FrenchSuitedDeck object.
     */
    public static function createFromSession(array $deck, Randomizer $randomizer, array $observers = []): FrenchSuitedDeck
    {
        $frenchDeck = new FrenchSuitedDeck($randomizer, $observers);
        // interesting enough, the card which are coming from session are restored as Card-objects, while the Deck is an Array.
        foreach ($deck as $card) {
            $frenchDeck->addCard($card);
        }
        $frenchDeck->notify();
        return $frenchDeck;
    }

    public function sort(): void
    {
        usort($this->cards, [$this, 'sortBySuitAndValue']);
        $this->notify();
    }

    public function sortBySuit(Card $cardA, Card $cardB): int
    {
        $sortingOrder = ['Spade', 'Heart', 'Diamond', 'Club'];
        return array_search($cardA->getSuit(), $sortingOrder) <=> array_search($cardB->getSuit(), $sortingOrder);
    }

    public function sortByValue(Card $cardA, Card $cardB): int
    {
        if ($cardA->getAlternativeValue() && $cardB->getAlternativeValue()) {
            return $cardA->getAlternativeValue() <=> $cardB->getAlternativeValue();
        }
        if ($cardA->getAlternativeValue() && !$cardB->getAlternativeValue()) {
            return $cardA->getAlternativeValue() <=> $cardB->getValue();
        }

        if (!$cardA->getAlternativeValue() && $cardB->getAlternativeValue()) {
            return $cardA->getValue() <=> $cardB->getAlternativeValue();
        }

        return $cardA->getValue() <=> $cardB->getValue();
    }

    public function sortBySuitAndValue(Card $cardA, Card $cardB): int
    {
        $suitComparison = $this->sortBySuit($cardA, $cardB);
        if ($suitComparison === 0) {
            return $this->sortByValue($cardA, $cardB);
        }
        return $suitComparison;
    }
}
