<?php

namespace App\Model;

use Exception;

class CardDealer
{
    /**
     * Deal cards to players.
     *
     * @param DeckOfCards $deck The deck of cards to deal from.
     * @param int $numberOfPlayers The number of players to deal to.
     * @param int $cardsPerPlayer The number of cards to deal to each player.
     *
     * @return array<CardHand> The hands of the players.
     */
    public function deal(DeckOfCards $deck, int $numberOfPlayers, int $cardsPerPlayer): array
    {
        $hands = [];
        for ($i = 0; $i < $numberOfPlayers; $i++) {
            $hands[] = new CardHand();
        }

        for ($i = 0; $i < $cardsPerPlayer; $i++) {
            foreach ($hands as $hand) {
                if (!$deck->cardCollection->getNumberOfCards() > 0) {
                    throw new Exception('Not enough cards to deal');
                }
                $hand->addCard($deck->cardCollection->drawCards(1)[0]);
            }
        }

        return $hands;
    }
}
