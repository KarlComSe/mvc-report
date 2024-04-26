<?php

namespace App\Model;

use App\Model\DeckOfCards;
use App\Model\FrenchSuitedDeck;
use SplSubject;
use Random\Randomizer;
use SplObjectStorage;
use Exception;
use App\Model\BetManager;

class Game implements SplSubject
{
    private const VALID_FORM_NAMES = ['action', 'bet'];

    private $deck;
    private array $players;
    private string $currentPlayer;
    private string $gameStatus;
    public BetManager $betManager;
    private DetermineWinner $determineWinner;

    protected SplObjectStorage $observers;

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
        $this->setGameStatus($gameStatus);
        if (count($players) > 0) {
            $this->currentPlayer = $players[0]->getName();
        }
        $this->determineWinner = new DetermineWinner();


        $this->notify();
    }

    public function attach(\SplObserver $observer): void
    {
        $this->observers->attach($observer);
    }

    public function detach(\SplObserver $observer): void
    {
        $this->observers->detach($observer);
    }

    public function notify(): void
    {
        foreach ($this->observers as $observer) {
            $observer->update($this->getGameState());
        }
    }

    public static function createFromSavedState(array $gameState): Game
    {
        $game = new Game();
        $game->deck = FrenchSuitedDeck::createFromSession($gameState['deck'], new Randomizer());
        foreach ($gameState['players'] as $player) {
            $game->players[$player->getName()] = $player;
        }
        $game->betManager = new BetManager($gameState['pot']);
        $game->currentPlayer = $gameState['currentPlayer'];
        $game->setGameStatus($gameState['gameStatus']);
        return $game;
    }

    public function getGameState(): array
    {
        return [
            'deck' => $this->deck->getDeck(),
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

    public function playRound(array $formData): void
    {

        $this->isValidForm($formData);

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

    private function processMove(array $formData): void
    {
        $action = $this->players[$this->currentPlayer] instanceof HumanPlayer ?
            $formData['action'] :
            $this->players[$this->currentPlayer]->makeMove();

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
        $this->deck = FrenchSuitedDeck::create(new Randomizer());
        $this->deck->shuffle();
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

    public function getPlayers(): array
    {
        return $this->players;
    }

    private function isValidForm(array $formData): void
    {
        foreach ($formData as $key => $value) {
            if (!in_array($key, self::VALID_FORM_NAMES)) {
                throw new Exception('Invalid form name.');
            }

            // ignorantly accepting all values...
        }
    }

    public function dealCard(): void
    {
        $this->players[$this->currentPlayer]->addCardToHand($this->deck->drawCard());
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

    public function hasNextPlayer(): bool
    {
        $playerIndex = array_search($this->currentPlayer, array_keys($this->players));
        return $playerIndex < count($this->players) - 1;
    }
}
