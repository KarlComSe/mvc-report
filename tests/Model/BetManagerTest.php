<?php

namespace App\Model;

use PHPUnit\Framework\TestCase;
use Exception;

/**
 * Test cases for class BetManager.
 */
class BetManagerTest extends TestCase
{
    /**
     * Construct object and verify that the object is of the expected
     * type.
     */
    public function testCreateBetManagerWithNoArguments(): void
    {
        $betManager = new BetManager();
        $this->assertInstanceOf("\App\Model\BetManager", $betManager);
    }

    /**
     * Assert that an initiated Bet Manager has no pot.
     */

    public function testGetPot(): void
    {
        $betManager = new BetManager();
        $res = $betManager->getPot();
        $exp = null;
        $this->assertEquals($exp, $res);
    }

    /**
     * Assert that an initiated Bet Manager has no pot.
     */

    public function testHasPot(): void
    {
        $betManager = new BetManager();
        $res = $betManager->hasPot();
        $this->assertFalse($res);
    }

    /**
     * Assert that a payout of an empty pot doesn't impact player balance.
     */

    public function testPayoutEmptyPot(): void
    {
        $betManager = new BetManager();
        $player = new HumanPlayer("testPlayer", 0);
        $betManager->payout(new HumanPlayer());
        $res = $player->getBalance();
        $exp = 0;
        $this->assertEquals($exp, $res);
    }

    /**
     * Assert that adding a pot to a non empty pot throws an exception.
     *
     * @throws Exception
     */

    public function testSetPot(): void
    {
        $betManager = new BetManager();
        $betManager->setPot(100, [new HumanPlayer()]);
        $this->expectException(Exception::class);
        $betManager->setPot(100, [new HumanPlayer()]);
    }
}
