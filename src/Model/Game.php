<?php

namespace App\Model;

use App\Model\DeckOfCards;
use App\Model\FrenchSuitedDeck;
use App\Model\DeckShuffler;
use SplSubject;
use Random\Randomizer;
use SplObjectStorage;
use Exception;
use App\Model\BetManager;
use SplObserver;

class Game implements SplSubject
{

    private ?DeckOfCards $deck;
    /**
     * @var array<?Player> The players of the game.
     */
    private array $players;
    private string $currentPlayer;
    private string $gameStatus;
    public BetManager $betManager;
    private DetermineWinner $determineWinner;

    protected SplObjectStorage $observers;
    /**
     * Constructor for the Game class.
     * @param array<?Player> $players The players of the game.
     * @param DeckOfCards|null $deck The deck of cards to be used in the game.
     * @param array<SplObserver> $observers The observers to be attached to the game.
     * @param string $gameStatus The status of the game.
     */
    public function __construct(
        array $players = [],
        DeckOfCards $deck = null,
        array $observers = [],
        string $gameStatus = 'ongoing'
    ) {

        $this->observers = new SplObjectStorage();

        foreach ($observers as $observer) {
            $this->attach($observer);
        }
        $this->betManager = new BetManager();
        $this->deck = $deck;
        $this->players = $players;

        if (count($players) > 0) {
            $this->currentPlayer = $players[0]->getName();
        }
        $this->setGameStatus($gameStatus);
        $this->determineWinner = new DetermineWinner();


        $this->notify();
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
            $observer->update($this->getGameState());
        }
    }

    /**
     * Creates a new Game object from a saved game state.
     *
     * @param array<string, mixed> $gameState The saved game state.
     * @return Game The newly created Game object.
     */
    public static function createFromSavedState(array $gameState): Game
    {
        $game = new Game();
        $game->deck = FrenchSuitedDeck::createFromSession($gameState['deck']);
        foreach ($gameState['players'] as $player) {
            $game->players[$player->getName()] = $player;
        }
        $game->betManager = new BetManager($gameState['pot']);
        $game->currentPlayer = $gameState['currentPlayer'];
        $game->setGameStatus($gameState['gameStatus']);
        return $game;
    }

    /**
     * Get the current state of the game.
     *
     * @return array<string, mixed> The game state, including the deck, players, current player, pot, and game status.
     */
    public function getGameState(): array
    {
        return [
            'deck' => $this->deck ? $this->deck->getDeck() : null,
            'players' => $this->players,
            'currentPlayer' => $this->currentPlayer,
            'pot' => $this->betManager->getPot(),
            'gameStatus' => $this->getGameStatus()
        ];
    }

    public function getGameStatus(): string
    {
        return $this->gameStatus;
    }

    private function setGameStatus(string $gameStatus): void
    {
        $this->gameStatus = $gameStatus;
        $this->notify();
    }

    /**
     * Plays a round of the game based on the provided form data.
     *
     * @param array<string, string> $formData The form data containing the player's move and action.
     * @return void
     * 
     * @throws Exception If the form data is invalid.
     */
    public function playRound(array $formData): void
    {
        $formValidator = new GameFormValidator();
        $formValidator->isValidForm($formData);

        if ($formData['action'] !== 'restart') {
            $this->processMove($formData);

            $winner = $this->determineWinner->getWinner($this->players, $this->currentPlayer);

            if ($winner !== null) {
                $this->betManager->payOut($this->players[$winner]);
                $this->setGameStatus('ended');
                $this->notify();
                return;
            }
        }

        if ($formData['action'] == 'restart') {
            $this->reset();
            return;
        }
    }

    public function getWinnerBasedOnHand(): string
    {
        return $this->determineWinner->getWinnerBasedOnHand($this->players);
    }

    /**
     * Process the move based on the given form data.
     *
     * @param array<string, mixed> $formData The form data containing the action and bet (if applicable).
     * @return void
     * @throws Exception If the action is invalid.
     */
    private function processMove(array $formData): void
    {
        $action = $this->players[$this->currentPlayer] instanceof HumanPlayer ?
            $formData['action'] :
                ($this->players[$this->currentPlayer] instanceof AiPlayerInterface ?
                    $this->players[$this->currentPlayer]->makeMove() :
                    throw new Exception('Invalid player type.'));
        switch ($action) {
            case 'hit':
                $this->dealCard();
                break;
            case 'stand':
                $this->players[$this->currentPlayer]->stand();
                $this->notify();
                if ($this->hasNextPlayer()) {
                    $this->nextPlayer();
                }
                break;
            case 'bet':
                $this->betManager->setPot($formData['bet'], $this->players);
                $this->notify();
                break;
            default:
                throw new Exception('Invalid action.');
        }
    }

    private function reset(): void
    {
        if (!$this->isRestartable()) {
            throw new Exception('Cannot restart when there are money in the pot.');
        }
        $this->setGameStatus('ongoing');
        $this->deck = FrenchSuitedDeck::create();
        $deckShuffler = new DeckShuffler(new Randomizer());
        $deckShuffler->shuffle($this->deck);
        foreach ($this->players as $player) {
            $player->resetHand();
            $player->resetStanding();
        }
        $this->currentPlayer = 'human';
        $this->notify();
    }

    public function isRestartable(): bool
    {
        return $this->betManager->getPot() === null;
    }

    /**
     * Get the players of the game.
     *
     * @return array<Player> The array of players.
     */
    public function getPlayers(): array
    {
        return $this->players;
    }

    // /**
    //  * Validates the form data.
    //  *
    //  * @param array<string, string> $formData The form data to validate.
    //  *
    //  * @throws Exception If an invalid form name is found.
    //  */
    // private function isValidForm(array $formData): void
    // {
    //     $formKeys = array_keys($formData);
    //     foreach ($formKeys as $key) {
    //         if (!in_array($key, self::VALID_FORM_NAMES)) {
    //             throw new Exception('Invalid form name.');
    //         }

    //         // ignorantly accepting all values...
    //     }
    // }

    public function dealCard(): void
    {
        if ($this->deck === null) {
            throw new Exception('Cannot deal cards from a non existing deck.');
        }
        $this->players[$this->currentPlayer]->addCardsToHand($this->deck->cardCollection->drawCards(1));
        $this->notify();
    }

    public function getCurrentPlayer(): string
    {
        return $this->currentPlayer;
    }

    public function nextPlayer(): void
    {
        if (!$this->hasNextPlayer()) {
            throw new Exception('No next player.');
        }
        $playerIndex = array_search($this->currentPlayer, array_keys($this->players));
        $nextPlayerIndex = $playerIndex + 1;
        $this->currentPlayer = array_keys($this->players)[$nextPlayerIndex];
        $this->notify();
    }

    private function hasNextPlayer(): bool
    {
        $playerIndex = array_search($this->currentPlayer, array_keys($this->players));
        return $playerIndex < count($this->players) - 1;
    }
}
