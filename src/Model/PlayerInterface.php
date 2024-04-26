<?php

namespace App\Model;

interface PlayerInterface
{
    public function getHand(): array;
    public function addCardToHand(Card $card): void;
    public function getScores(): array;
    public function resetHand(): void;
    public function resetStanding(): void;
    public function getName(): string;
    public function stand(): void;
    public function addMoney(int $amount): void;
    public function placeBet(int $amount): void;
    public function getBalance(): int;
}
