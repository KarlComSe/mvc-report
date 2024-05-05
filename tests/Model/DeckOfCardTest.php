<?php

namespace App\Model;

use App\Observer\SessionSavingObserver;
use PHPUnit\Framework\TestCase;
use Random\Randomizer;
use Symfony\Component\HttpFoundation\Request;

/**
 * Test cases for class FrenchSuitedDeck.
 */
class FrenchSuitedDeckTest extends TestCase
{
    /**
     * Construct object and verify that the object is of the expected
     * type.
     */
    public function testCreateDeckOfCards(): void
    {
        $randomizer = new Randomizer();
        $observer = new SessionSavingObserver(new Request(), "data");
        $deck = new FrenchSuitedDeck($randomizer, [$observer]);
        $this->assertInstanceOf("\App\Model\DeckOfCards", $deck);
    }

    /** 
     * Assert that create method returns a FrenchSuitedDeck object.
     */

    public function testCreate(): void
    {
        $randomizer = new Randomizer();
        $deck = FrenchSuitedDeck::create($randomizer);
        $this->assertInstanceOf("\App\Model\FrenchSuitedDeck", $deck);
    }

    /** 
     * Assert that getNumberOfCards() return 52 for a french deck
     */

    public function testGetNumberOfCards(): void
    {
        $randomizer = new Randomizer();
        $deck = FrenchSuitedDeck::create($randomizer);
        $this->assertEquals($deck->getNumberOfCards(), 52);
    }

    /**
     * Assert that dealCards() deals cards.
     */

    public function testDealCards(): void
    {
        $randomizer = new Randomizer();
        $deck = FrenchSuitedDeck::create($randomizer);
        $cardHands = $deck->dealCards(2, 2);
        $this->assertEquals(count($cardHands[0]->getHand()), 2);
        $this->assertInstanceOf("\App\Model\CardHand", $cardHands[0]);
        $this->assertEquals(count($deck->cards), 48);
    }

    /**
     * Assert that drawCards throws an exception when 
     * trying to draw more cards than available.
     */

    public function testDrawCards(): void
    {
        $randomizer = new Randomizer();
        $deck = FrenchSuitedDeck::create($randomizer);
        $this->expectException(\Exception::class);
        $deck->drawCards(53);
    }

    /**
     * Assert that observers are attached to the deck.
     */
    public function testAttach(): void
    {
        $randomizer = new Randomizer();
        $deck = FrenchSuitedDeck::create($randomizer);
        $observer = new SessionSavingObserver(new Request(), "data");
        $deck->attach($observer);
        $this->assertEquals($deck->getObservers()->count(), 1);

        $deck->detach($observer);
        $this->assertEquals($deck->getObservers()->count(), 0);
    }

    // /**
    //  * Assert that createFromSession method returns a FrenchSuitedDeck object.
    //  */

    // public function testCreateFromSession()
    // {
    //     $randomizer = new Randomizer();
    //     $deck = FrenchSuitedDeck::createFromSession([], $randomizer);
    //     $this->assertInstanceOf("\App\Model\FrenchSuitedDeck", $deck);
    // }

    /**
     * Assert that sort method returns a sorted deck.
     */

    public function testSort(): void
    {
        $randomizer = new Randomizer();
        $deck = FrenchSuitedDeck::create($randomizer);
        $deck->shuffle();
        $deck->sort();
        $this->assertEquals($deck->cards[0]->getSuit(), "Spade");
        $this->assertEquals($deck->cards[0]->getValue(), "2");
        $this->assertEquals($deck->cards[1]->getSuit(), "Spade");
        $this->assertEquals($deck->cards[1]->getValue(), "3");
        $this->assertEquals($deck->cards[51]->getSuit(), "Club");
        $this->assertEquals($deck->cards[51]->getValue(), "1");
        $this->assertEquals($deck->cards[51]->getAlternativeValue(), "14");
    }


    /**
     * Assert that sortBySuit method returns a sorted deck by suit.
     */

    public function testSortBySuit(): void
    {
        $randomizer = new Randomizer();
        $deck = FrenchSuitedDeck::create($randomizer);
        $deck->shuffle();
        $deck->sort();
        $deck->sortBySuit($deck->cards[0], $deck->cards[1]);
        $this->assertEquals($deck->cards[0]->getSuit(), "Spade");
        $this->assertEquals($deck->cards[0]->getValue(), "2");
        $this->assertEquals($deck->cards[1]->getSuit(), "Spade");
        $this->assertEquals($deck->cards[1]->getValue(), "3");
        $this->assertEquals($deck->cards[51]->getSuit(), "Club");
    }

    /**
     * Assert that sortByValue method returns a sorted deck by value.
     */

    public function testSortByValue(): void
    {
        $randomizer = new Randomizer();
        $deck = FrenchSuitedDeck::create($randomizer);
        $deck->sort();
        $res = $deck->sortByValue($deck->cards[10], $deck->cards[1]);
        $this->assertEquals($res, 1);

        // equal
        $res = $deck->sortByValue($deck->cards[0], $deck->cards[13]);
        $this->assertEquals($res, 0);

        $res = $deck->sortByValue($deck->cards[0], $deck->cards[1]);

        $this->assertEquals($res, -1);

        $res = $deck->sortByValue($deck->cards[12], $deck->cards[25]);
        $this->assertEquals($res, 0);
        // var_dump($deck->cards[12]->getAlternativeValue(), $deck->cards[25]->getAlternativeValue());
        // ob_flush();
    }

    /**
     * Assert that sortBySuitAndValue method returns a sorted deck by suit and value.
     */

    public function testSortBySuitAndValue(): void
    {
        $randomizer = new Randomizer();
        $deck = FrenchSuitedDeck::create($randomizer);
        $deck->sort();
        $res = $deck->sortBySuitAndValue($deck->cards[0], $deck->cards[1]);
        $this->assertEquals($res, -1);

        // equal
        $res = $deck->sortBySuitAndValue($deck->cards[0], $deck->cards[13]);
        $this->assertEquals($res, -1);

        $res = $deck->sortBySuitAndValue($deck->cards[1], $deck->cards[0]);
        $this->assertEquals($res, 1);
    }
}
