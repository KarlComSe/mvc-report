<?php

namespace App\Model;

use Exception;

class CardDealer
{
    public function deal(DeckOfCards $deck, int $numberOfPlayers, int $cardsPerPlayer): array
    {
        $hands = [];
        for ($i = 0; $i < $numberOfPlayers; $i++) {
            $hands[] = new CardHand();
        }

        for ($i = 0; $i < $cardsPerPlayer; $i++) {
            foreach ($hands as $hand) {
                if (!$deck->hasCards()) {
                    throw new Exception('Not enough cards to deal');
                }
                $hand->addCard($deck->drawCards(1)[0]);
            }
        }

        return $hands;
    }
}
