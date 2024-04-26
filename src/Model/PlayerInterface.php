<?php

namespace App\Model;

/**
 * Interface PlayerInterface
 * Represents a player in a card game.
 */
interface PlayerInterface
{
    /**
     * Get the player's hand.
     *
     * @return array<Card> The player's hand.
     */
    public function getHand(): array;

    /**
     * Add a card to the player's hand.
     *
     * @param Card $card The card to add.
     * @return void
     */
    public function addCardToHand(Card $card): void;

    /**
     * Get the player's scores.
     *
     * @return array<int> The player's scores.
     */
    public function getScores(): array;

    /**
     * Reset the player's hand.
     *
     * @return void
     */
    public function resetHand(): void;

    /**
     * Reset the player's standing.
     *
     * @return void
     */
    public function resetStanding(): void;

    /**
     * Get the player's name.
     *
     * @return string The player's name.
     */
    public function getName(): string;

    /**
     * Make the player stand.
     *
     * @return void
     */
    public function stand(): void;

    /**
     * Add money to the player's balance.
     *
     * @param int $amount The amount of money to add.
     * @return void
     */
    public function addMoney(int $amount): void;

    /**
     * Place a bet from the player's balance.
     *
     * @param int $amount The amount of money to bet.
     * @return void
     */
    public function placeBet(int $amount): void;

    /**
     * Get the player's balance.
     *
     * @return int The player's balance.
     */
    public function getBalance(): int;
}
