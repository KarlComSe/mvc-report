<?php

namespace App\Model;

use PHPUnit\Framework\TestCase;
use Exception;

/**
 * Test cases for class Card.
 */
class DetermineWinnerTest extends TestCase
{
    /**
     * Construct object and verify that the object is of the expected
     * type.
     */
    public function testCreate(): void
    {
        $determineWinner = new DetermineWinner();
        $this->assertInstanceOf("\App\Model\DetermineWinner", $determineWinner);
    }

    /**
     * Assert that the function returns the correct winner.
     */

    public function testDetermineWinner(): void
    {
        $determineWinner = new DetermineWinner();
        $player = new HumanPlayer();
        $dealer = new BankPlayer();
        $player->addCardsToHand([new Card("hearts", "ace", 1, 14), new Card("hearts", "ace", 1, 14)]);
        $dealer->addCardsToHand([new Card("hearts", "ace", 1, 14), new Card("hearts", "ace", 1, 14)]);
        $player->stand();
        $dealer->stand();
        $res = $determineWinner->getWinner(["human" => $player, "bank" => $dealer], 'bank');
        $exp = "bank";
        $this->assertEquals($exp, $res);
    }

    /**
     * Assert that the function returns null if not all player has stood.
     */

    public function testDetermineWinnerNull(): void
    {
        $determineWinner = new DetermineWinner();
        $player = new HumanPlayer();
        $dealer = new BankPlayer();
        $player->addCardsToHand([new Card("hearts", "ace", 1, 14), new Card("hearts", "ace", 1, 14)]);
        $dealer->addCardsToHand([new Card("hearts", "ace", 1, 14), new Card("hearts", "ace", 1, 14)]);
        $player->stand();
        $res = $determineWinner->getWinner(["human" => $player, "bank" => $dealer], 'bank');
        $this->assertNull($res);
    }

    /**
     * Assert that the function returns human when bank is busted.
     */

    public function testGetWinnerBankBusted(): void
    {
        $determineWinner = new DetermineWinner();
        $dealer = new BankPlayer();
        $dealer->addCardsToHand([new Card("", "", 13), new Card("", "", 13)]);

        $exp = "human";
        $res = $determineWinner->getWinner(["bank" => $dealer], 'bank');
        $this->assertEquals($exp, $res);
    }

    /**
     * Assert that isBusted returns true if the player is busted.
     */

    public function testIsBusted(): void
    {
        $determineWinner = new DetermineWinner();
        $player = new HumanPlayer();
        $player->addCardsToHand([new Card("", "", 13), new Card("", "", 13)]);
        $res = $determineWinner->isBusted($player->getRealHand());
        $this->assertTrue($res);
    }
}
