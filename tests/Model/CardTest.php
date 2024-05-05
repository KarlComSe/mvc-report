<?php

namespace App\Model;

use PHPUnit\Framework\TestCase;
use Exception;

/**
 * Test cases for class Card.
 */
class CardTest extends TestCase
{
    /**
     * Construct object and verify that the object is of the expected
     * type.
     */
    public function testCreateCard(): void
    {
        $card = new Card("hearts", "ace", 1, 14);
        $this->assertInstanceOf("\App\Model\Card", $card);
    }

    /**
     * Assert that a card returns the correct details.
     */

    public function testGetCard(): void
    {
        $card = new Card("hearts", "ace", 1, 14);
        $res = $card->getCard();
        $exp = ["hearts", "ace", 1, 14];
        $this->assertEquals($exp, $res);
    }

    /**
     * Assert that getValue returns correct value.
     */

    public function testGetValue(): void
    {
        $card = new Card("hearts", "ace", 1, 14);
        $res = $card->getValue();
        $exp = 1;
        $this->assertEquals($exp, $res);
    }

    /**
     * Assert that getAlternativeValue returns correct value.
     */

    public function testGetAlternativeValue(): void
    {
        $card = new Card("hearts", "ace", 1, 14);
        $res = $card->getAlternativeValue();
        $exp = 14;
        $this->assertEquals($exp, $res);
    }

    /**
     * Assert that getSuit returns correct suit.
     */

    public function testGetSuit(): void
    {
        $card = new Card("hearts", "ace", 1, 14);
        $res = $card->getSuit();
        $exp = "hearts";
        $this->assertEquals($exp, $res);
    }

    /**
     * Assert that getUnicodeRepresentation returns correct unicode representation.
     *
     */

    public function testGetUnicodeRepresentation(): void
    {
        $res = Card::getUnicodeRepresentation(1, "heart");
        $exp = "🂱";
        $this->assertEquals($exp, $res);
    }
}
