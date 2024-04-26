<?php

namespace App\Model;

use App\Model\CardHand;

class DetermineWinner
{
    public function __construct()
    {
    }

    public function getWinner(array $players, string $currentPlayer): ?string
    {
        // this logic is halting, but works in this case... for now.
        if ($this->isBusted($players[$currentPlayer]->getRealHand())) {
            if ($currentPlayer === "bank") {
                return "human";
            }
            return "bank";
        }

        if ($this->allPlayersStand($players)) {
            return $this->getWinnerBasedOnHand($players);
        }

        return null;
    }

    private function allPlayersStand(array $players): bool
    {
        foreach ($players as $player) {
            if (!$player->isStanding()) {
                return false;
            }
        }

        return true;
    }

    public function isBusted(CardHand $hand): bool
    {
        $scoreArray = $hand->getScore();
        if (empty($scoreArray)) {
            return false;
        }
        if (min($scoreArray) > 21) {
            return true;
        }
        return false;
    }

    public function getWinnerBasedOnHand(array $players): string
    {
        $scores = [];
        foreach ($players as $player) {
            $scores[$player->getName()] = $player->getScores();
        }

        $bestScores = $this->getPlayersBestScores($scores);
        $winners = $this->getAllHighestScores($bestScores);

        if (count($winners) === 1) {
            return $winners[0];
        }

        return "bank";

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
