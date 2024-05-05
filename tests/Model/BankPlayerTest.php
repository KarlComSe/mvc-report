<?php

namespace App\Model;

use PHPUnit\Framework\TestCase;

/**
 * Test cases for class BankPlayer.
 */
class BankPlayerTest extends TestCase
{
    /**
     * Construct object and verify that the object is of the expected
     * type.
     */
    public function testCreateBankPlayerWithNoArguments(): void
    {
        $player = new BankPlayer();
        $this->assertInstanceOf("\App\Model\BankPlayer", $player);
    }

    /**
     * Verify that the player's name is 'bank'.
     */

    public function testGetName(): void
    {
        $player = new BankPlayer();
        $res = $player->getName();
        $exp = "bank";
        $this->assertEquals($exp, $res);
    }

    /**
     * Verify that the player's balance is 3000.
     */

    public function testGetBalance(): void
    {
        $player = new BankPlayer();
        $res = $player->getBalance();
        $exp = 3000;
        $this->assertEquals($exp, $res);
    }

    /**
     * Verify that the MakeMove-function returns hit as a first move.
     */

    public function testMakeMoveHit(): void
    {
        $player = new BankPlayer();
        $res = $player->makeMove();
        $exp = "hit";
        $this->assertEquals($exp, $res);
    }


    /**
     * Verify that the MakeMove-function returns stand as a move when
     * hand value exceeds 17.
     */

    public function testMakeMoveStand(): void
    {
        $player = new BankPlayer();
        $player->addCardsToHand([new Card("hearts", "whatever", 11), new Card("hearts", "whatever", 7)]);
        $res = $player->makeMove();
        $exp = "stand";
        $this->assertEquals($exp, $res);
    }


    /**
     * Verify previous bug, that the MakeMove-function returns stand as a move when
     * hand value is 22.
     */

    public function testMakeMoveStandFor22(): void
    {
        $player = new BankPlayer();
        $player->addCardsToHand([new Card("hearts", "whatever", 11), new Card("spade", "whatever", 11)]);

        $res = $player->makeMove();
        $exp = "stand";
        $this->assertEquals($exp, $res);
    }
}
