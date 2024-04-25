<?php

namespace App\Model;

abstract class Player implements PlayerInterface
{
    protected ?CardHand $hand;
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

    public function getHand(): array
    {
        return $this->hand->getHand();
    }

    public function addCardToHand(Card $card): void
    {
        $this->hand->addCard($card);
    }

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
