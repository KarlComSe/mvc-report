<?php

namespace App\Model;

use App\Model\DeckOfCards;
use Random\Randomizer;
use App\Model\Card;

class FrenchSuitedDeck extends DeckOfCards
{
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
        usort($this->cards, [$this, 'sortBySuiteAndValue']);
        $this->notify();
    }

    public function sortBySuite(Card $a, Card $b): int
    {
        $SORTING_ORDER = ['Spade', 'Heart', 'Diamond', 'Club'];
        return array_search($a->getSuit(), $SORTING_ORDER) <=> array_search($b->getSuit(), $SORTING_ORDER);
    }

    public function sortByValue(Card $a, Card $b): int
    {
        if ($a->getAlternativeValue() && $b->getAlternativeValue()) {
            return $a->getAlternativeValue() <=> $b->getAlternativeValue();
        }
        if ($a->getAlternativeValue() && !$b->getAlternativeValue()) {
            return $a->getAlternativeValue() <=> $b->getValue();
        }

        if (!$a->getAlternativeValue() && $b->getAlternativeValue()) {
            return $a->getValue() <=> $b->getAlternativeValue();
        }

        return $a->getValue() <=> $b->getValue();
    }

    public function sortBySuiteAndValue(Card $a, Card $b): int
    {
        $suiteComparison = $this->sortBySuite($a, $b);
        if ($suiteComparison === 0) {
            return $this->sortByValue($a, $b);
        }
        return $suiteComparison;
    }
}
