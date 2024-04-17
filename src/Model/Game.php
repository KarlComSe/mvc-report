<?php

namespace App\Model;

use App\Model\DeckOfCards;
use App\Model\FrenchSuitedDeck;
use App\Model\dealCardHand;
use SplSubject;
use Random\Randomizer;


class Game implements SplSubject
{
    public $hands = [];
    private $deck;
    private $currentPlayer = ['human', 'bank'];
    private ?int $playerBet = NULL;

    protected \SplObjectStorage $observers;

    private bool $ongoing;

    public function __construct($observers = [])
    {
        $this->ongoing = true;
        $this->hands = [
            'human' => new CardHand(),
            'bank' => new CardHand()
        ];
        $this->observers = new \SplObjectStorage();
        $this->deck = FrenchSuitedDeck::create(new Randomizer(), []);
        $this->deck->shuffle();
        foreach ($observers as $observer) {
            $this->attach($observer);
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
        foreach($this->observers as $observer) {
            $observer->update($this->getGameState());
        }
    }

    public static function createFromSavedState(array $gameState): Game
    {
        $game = new Game();
        $game->deck = FrenchSuitedDeck::createFromSession($gameState['deck'], new Randomizer());
        $game->hands = $gameState['hands'];
        $game->currentPlayer = $gameState['currentPlayer'];
        $game->playerBet = $gameState['playerBet'];
        $game->ongoing = $gameState['ongoing'];
        return $game;
    }

    public function getGameState(): array
    {
        return [
            'deck' => $this->deck->getDeck(),
            'hands' => $this->hands,
            'currentPlayer' => $this->currentPlayer,
            'playerBet' => $this->playerBet,
            'ongoing' => $this->ongoing
        ];
    }

    public function setPlayerBet(int $bet): void
    {
        if ($this->playerBet) {
            throw new \Exception('Player bet already set.');
        }
        $this->playerBet = $bet;
        $this->notify();
    }

    public function getPlayerBet(): ?int
    {
        return $this->playerBet;
    }

    public function dealCard(): void
    {
        if(!$this->isAllowedToDeal()) {
            throw new \Exception('Cannot deal cards.');
        }

        $this->hands[$this->getCurrentPlayer()]->addCard($this->deck->drawCard());
        $this->notify();
    }

    private function isAllowedToDeal(): bool
    {
        if ($this->ongoing && !$this->isBusted() && $this->playerBet) {
            return true;
        }
        return false;
    }

    public function isBusted(): bool
    {   
        $scoreArray = $this->hands[$this->getCurrentPlayer()]->getScore();
        if (empty($scoreArray)) {
            return false;
        }
        if (min($scoreArray) > 21){
            return true;
        }
        return false;
    }

    public function getCurrentPlayer(): string
    {
        if(count($this->currentPlayer)==0) {
            throw new \Exception('Rounds are over.');
        }
        return $this->currentPlayer[0];
    }

    public function nextPlayer(): void
    {
        if(count($this->currentPlayer)==0) {
            throw new \Exception('Rounds are over.');
        }
        $this->currentPlayer[] = array_shift($this->currentPlayer);
        $this->notify();
    }


    public function checkWinner(): string
    {
        if ($this->ongoing) {
            throw new \Exception('Game is not over yet.');
        }

        $humanScores = $this->hands['human']->getScore();
        $bankScores = $this->hands['bank']->getScore();

        if(min($humanScores) > 21) {
            return 'bank';
        }
        if(min($bankScores) > 21) {
            return 'human';
        }
        // get closest possible score for human to 21
        $humanBestScore = 0;
        foreach ($humanScores as $score){
            if ($score <= 21 && $score > $humanBestScore) {
                $humanBestScore = $score;
            }
        }

        $bankBestScore = 0;
        foreach ($bankScores as $score){
            if ($score <= 21 && $score > $bankBestScore) {
                $bankBestScore = $score;
            }
        }

        return $bankBestScore >= $humanBestScore ? "bank" : "human";
    }

    public function endGame(): void
    {
        $this->ongoing = false;
        $this->notify();
    }

    public function getGameStatus(): bool 
    {
        return $this->ongoing;
    }

}