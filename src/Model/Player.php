<?php

namespace App\Model;

abstract class Player implements PlayerInterface
{
    protected CardHand $hand;
    protected string $name;
    protected int $balance;
    protected bool $standing;
    protected int $roundsPlayed;

    public function __construct(
        string $name,
        int $balance,
        CardHand $hand = new CardHand(),
        int $roundsPlayed = 0,
        bool $standing = false
    ) {

        $this->name = $name;
        $this->balance = $balance;
        $this->hand = $hand;
        $this->roundsPlayed = $roundsPlayed;
        $this->standing = $standing;
    }

    /**
     * Get the player's hand.
     *
     * @return array<Card> The player's hand.
     */
    public function getHand(): array
    {
        if ($this->hand == null) {
            return [];
        }
        return $this->hand->getHand();
    }

    public function getRealHand(): CardHand
    {
        return $this->hand;
    }

    public function addCardToHand(Card $card): void
    {
        $this->hand->addCard($card);
    }

    /**
     * Get the scores of the player.
     *
     * @return array<int> The scores of the player.
     */
    public function getScores(): array
    {
        return $this->hand->getScore();
    }

    public function resetHand(): void
    {
        $this->hand = new CardHand();
    }

    public function resetStanding(): void
    {
        $this->standing = false;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function stand(): void
    {
        $this->standing = true;
        $this->roundsPlayed++;
    }

    public function isStanding(): bool
    {
        return $this->standing;
    }

    public function addMoney(int $amount): void
    {
        $this->balance += $amount;
    }

    public function placeBet(int $amount): void
    {
        $this->balance -= $amount;
    }

    public function getBalance(): int
    {
        return $this->balance;
    }
}
