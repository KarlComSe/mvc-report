<?php

namespace App\Model;

use App\Model\Player;
use Exception;

class BetManager
{
    public function __construct(private ?int $pot = null)
    {
    }

    // reset to "ended" removed.
    public function payOut(Player $player): void
    {
        $player->addMoney((int)$this->pot);
        $this->pot = null;
    }

    public function hasPot(): bool
    {
        return $this->pot !== null;
    }

    public function getPot(): ?int
    {
        return $this->pot;
    }

    /**
     * Sets the pot for the bet and deducts the bet amount from each player's balance.
     *
     * @param int $bet The amount of the bet.
     * @param Player[] $players An array of Player objects.
     * @throws Exception If the bet has already been placed or if the bet amount is negative.
     * @throws Exception If a player doesn't have enough money for the bet.
     */
    public function setPot(int $bet, array $players): void
    {
        if ($this->hasPot()) {
            throw new Exception('Bet already placed.');
        }
        if ($bet < 0) {
            throw new Exception('Bet must be positive.');
        }

        foreach ($players as $player) {
            if ($bet > $player->getBalance()) {
                throw new Exception($player->getName() . ' doesn\'t have enough money for this bet.');
            }
        }

        $this->pot = 0;

        foreach ($players as $player) {
            $player->placeBet($bet);
            $this->pot += $bet;
        }
    }
}
