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
            $winner = $this->getWinner();
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

    public function getWinner(): ?string
    {
        if ($this->isBusted()) {
            return $this->currentPlayer;
        }

        if ($this->allPlayersStand()) {
            return $this->getWinnerBasedOnHand();
        }

        return null;
    }

    private function allPlayersStand(): bool
    {
        foreach ($this->players as $player) {
            if (!$player->isStanding()) {
                return false;
            }
        }

        return true;

        // coPilot asked to make above more compact, suggest the following:
        // return array_reduce($this->players, function ($carry, $player) {
        //     return $carry && $player->isStanding;
        // }, true);
        // Interesting, but not great, and not readable. Would have preferred
        // something like this: $this->players->all(fn($player) => $player->isStanding);
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

    // public function hasBet(): bool
    // {
    //     return $this->bet !== null;
    // }

    // public function getBet(): ?int
    // {
    //     return $this->bet;
    // }

    // private function setBet(int $bet): void
    // {
    //     if ($this->hasBet()) {
    //         throw new Exception('Bet already placed.');
    //     }
    //     if ($bet < 0) {
    //         throw new Exception('Bet must be positive.');
    //     }

    //     foreach ($this->players as $player) {
    //         if ($bet > $player->getBalance()) {
    //             throw new Exception($player->getName() . ' doesn\'t have enough money for this bet.');
    //         }
    //     }

    //     $this->bet = 0;

    //     foreach ($this->players as $player) {
    //         $player->placeBet($bet);
    //         $this->bet += $bet;
    //     }

    //     $this->notify();
    // }

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

    public function isBusted(): bool
    {
        $scoreArray = $this->players[$this->currentPlayer]->getScores();
        if (empty($scoreArray)) {
            return false;
        }
        if (min($scoreArray) > 21) {
            return true;
        }
        return false;
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

    public function getWinnerBasedOnHand(): string
    {
        $scores = [];
        foreach ($this->players as $player) {
            $scores[$player->getName()] = $player->getScores();
        }

        $bestScores = $this->getPlayersBestScores($scores);
        $winners = $this->getAllHighestScores($bestScores);

        if (count($winners) === 1) {
            return $winners[0];
        }

        return "bank";

        // Possible to elaborate on the logic here...
        // if (array_key_exists('bank', $winners)) {
        //         return "bank";
        // }

        // throw new Exception('Undetermined winner.');
    }

    private function getPlayersBestScores(array $scores): array
    {
        $bestScores = [];

        foreach ($scores as $player => $playerScores) {
            $bestScore = 0;
            foreach ($playerScores as $score) {
                if ($score <= 21 && $score > $bestScore) {
                    $bestScore = $score;
                }
            }
            $bestScores[$player] = $bestScore;
        }

        return $bestScores;
    }

    private function getAllHighestScores(array $bestScores): array
    {
        $maxScore = max($bestScores);
        $winners = [];
        foreach ($bestScores as $player => $score) {
            if ($score === $maxScore) {
                $winners[] = $player;
            }
        }

        return $winners;
    }
}
