<?php

namespace App\Model;

use PHPUnit\Framework\TestCase;

/**
 * Test cases for class GrahicCard.
 */
class GraphicCardTest extends TestCase
{
    /**
     * Construct object and verify that the object is of the expected
     * type.
     */
    public function testCreateCard(): void
    {
        $card = new GraphicCard("hearts", "ace", 1, "🂱", 14);
        $this->assertInstanceOf("\App\Model\GraphicCard", $card);
    }

    /**
     * Assert that a card returns the correct details.
     */

    public function testGetCard(): void
    {
        $card = new GraphicCard("hearts", "ace", 1, "🂱", 14);
        $res = $card->getCard();
        $exp = ["hearts", "ace", 1, 14, "🂱"];
        $this->assertEquals($exp, $res);
    }
}
