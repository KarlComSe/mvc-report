<?php

namespace App\Model;

use App\Model\CardHand;

class DetermineWinner
{
    public function __construct()
    {
    }

    /**
     * Get the winner from the given array of players.
     *
     * @param array<Player> $players An array of player names.
     * @param string $currentPlayer The name of the current player.
     * @return string|null The name of the winner, or null if there is no winner.
     */
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

    /**
     * Checks if all players are satisfied.
     *
     * @param array<Player> $players The array of players.
     * @return bool Returns true if all players are satisfied, false otherwise.
     */
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

    /**
     * Determines the winner based on the players' hands.
     * Assumes that all players are satisfied.
     * Doesn't check if any player is busted.
     *
     * @param array<Player> $players An array of players.
     * @return string The name of the winning player.
     */
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

    /**
     * Filters out the best scores for each player.
     *
     * @param array<string, array<int>> $scores The scores for each player,
     * e.g. ['human' => [21, 22], 'bank' => [20, 21]].
     * @return array<string, int> The best scores for each player, e.g. ['human' => 21, 'bank' => 20].
     */
    private function getPlayersBestScores(array $scores): array
    {
        return array_map(function ($playerScores) {
            return array_reduce($playerScores, function ($bestScore, $score) {
                return ($score <= 21 && $score > $bestScore) ? $score : $bestScore;
            }, 0);
        }, $scores);
    }

    /**
     * Returns an array of players with the highest scores.
     *
     * @param array<string, int> $bestScores An array containing the best score of each player.
     * @return array<string> An array containing the names of the players with the highest scores.
     */
    private function getAllHighestScores(array $bestScores): array
    {
        $maxScore = max($bestScores);
        return array_keys(array_filter($bestScores, fn ($score) => $score === $maxScore));
    }
}
