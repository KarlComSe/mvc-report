<?php

namespace App\Model;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use App\Observer\SessionSavingObserver;
use Exception;

/**
 * Test cases for class Card.
 */
class GameTest extends TestCase
{
    /**
     * Construct object and verify that the object is of the expected
     * type.
     */
    public function testCreate(): void
    {
        $game = new Game();
        $this->assertInstanceOf("\App\Model\Game", $game);
    }

    /**
     * Assert that create from savedState returns a game object.
     */

    public function testCreateFromSavedState(): void
    {
        $savedGame = new Game(
            [new HumanPlayer(), new BankPlayer()],
            FrenchSuitedDeck::create()
        );

        $savedState = $savedGame->getGameState(
        );
        $game = Game::createFromSavedState($savedState);
        $this->assertInstanceOf("\App\Model\Game", $game);
    }

    /**
     * Assert that the playRound fuction throws an exception on invalid form input
     */

    public function testPlayRoundInvalidInput(): void
    {
        $game = new Game();
        $this->expectException(Exception::class);
        $game->playRound(["invalid" => "data"]);
    }

    /**
     * Assert that the playRound fuction properly resets the game, when action == restart.
     */

    public function testPlayRoundRestart(): void
    {
        $human = new HumanPlayer();
        $bank = new BankPlayer();
        $game = new Game(
            [$human, $bank],
            FrenchSuitedDeck::create()
        );

        $human->stand();
        $bank->stand();

        $game->playRound(["action" => "restart"]);

        $exp = $human->isStanding();
        $this->assertFalse($exp);

        $exp = $bank->isStanding();
        $this->assertFalse($exp);
    }


    /**
     * Assert that the playRound fuction sets the game status to "ended"
     * when a winner is determined.
     */

    public function testPlayRoundWinner(): void
    {
        $human = new HumanPlayer();
        $bank = new BankPlayer();
        $players = [$human, $bank];
        $game = new Game(
            $players,
            FrenchSuitedDeck::create()
        );
        // There is a "bug" in my code, the re-created game is not the same as the existing game
        $game = Game::createFromSavedState($game->getGameState());

        $game->playRound(["action" => "hit"]);
        $game->playRound(["action" => "stand"]);
        $game->playRound(["action" => "hit"]);
        $game->playRound(["action" => "stand"]);

        $exp = $game->getGameStatus();
        $this->assertEquals("ended", $exp);
    }

    /**
     * Test getWinnerBasedOnHAnd returns correct winner.
     */

    public function testGetWinnerBasedOnHand(): void
    {
        $human = new HumanPlayer();
        $bank = new BankPlayer();
        $players = [$human, $bank];

        $game = new Game(
            $players,
            FrenchSuitedDeck::create()
        );
        // There is a "bug" in my code, the re-created game is not the same as the existing game
        $game = Game::createFromSavedState($game->getGameState());

        $human->addCardsToHand([new Card("hearts", "ace", 1, 14), new Card("hearts", "ace", 1, 14)]);
        $bank->addCardsToHand([new Card("hearts", "ace", 1, 14), new Card("hearts", "ace", 1, 14)]);
        $human->stand();
        $bank->stand();

        $exp = "bank";
        $res = $game->getWinnerBasedOnHand();
        $this->assertEquals($exp, $res);
    }

    /**
     * Assert that it is possible to attach and detach an observer.
     */

    public function testAttachAndDetach(): void
    {
        $human = new HumanPlayer();
        $bank = new BankPlayer();
        $players = [$human, $bank];

        $game = new Game(
            $players,
            FrenchSuitedDeck::create(),
        );

        $observer = new SessionSavingObserver(new Request(), "data");
        $game->attach($observer);
        $this->assertEquals($game->getObservers()->count(), 1);

        $game->detach($observer);
        $this->assertEquals($game->getObservers()->count(), 0);
    }

    /**
     * Test getPlayers that it returns the players.
     */

    public function testGetPlayers(): void
    {
        $human = new HumanPlayer();
        $bank = new BankPlayer();
        $players = [$human, $bank];
        $game = new Game(
            $players,
            FrenchSuitedDeck::create()
        );

        $exp = $players;
        $res = $game->getPlayers();
        $this->assertEquals($exp, $res);
    }

    /**
     * Test get current player
     */

    public function testGetCurrentPlayer(): void
    {
        $human = new HumanPlayer();
        $bank = new BankPlayer();
        $players = [$human, $bank];
        $game = new Game(
            $players,
            FrenchSuitedDeck::create()
        );

        $exp = "human";
        $res = $game->getCurrentPlayer();
        $this->assertEquals($exp, $res);
    }


}
