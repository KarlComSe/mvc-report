<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

use App\Model\DeckShuffler;
use App\Model\CardDealer;
use App\Model\FrenchSuitedDeck;
use App\Observer\SessionSavingObserver;
use Random\Randomizer;

class DeckService
{
    public function getInstansiatedDeck(Request $request, string $deckName): FrenchSuitedDeck
    {
        $observers = [
            new SessionSavingObserver($request, $deckName)
        ];
        return FrenchSuitedDeck::createFromSession($request->getSession()->get($deckName), $observers);
    }

    public function getDeck(Request $request): JsonResponse
    {
        $deck = $this->getInstansiatedDeck($request, 'deck_2');
        $deck->sort();

        return new JsonResponse($deck->getDeck());
    }

    public function shuffleDeck(Request $request): JsonResponse
    {
        $deck = $this->getInstansiatedDeck($request, 'deck_2');
        $deckShuffler = new DeckShuffler(new Randomizer());
        $deckShuffler->shuffle($deck);

        return new JsonResponse($deck->getDeck());
    }

    public function drawCard(Request $request): JsonResponse
    {
        $deck = $this->getInstansiatedDeck($request, 'deck_2');
        $card = $deck->cardCollection->drawCards(1);

        return new JsonResponse($card);
    }

    public function drawCards(int $number, Request $request): JsonResponse
    {
        $deck = $this->getInstansiatedDeck($request, 'deck_2');
        $cards = $deck->cardCollection->drawCards($number);

        return new JsonResponse($cards);
    }

    public function dealCards(int $players, int $cards, Request $request): JsonResponse
    {
        $deck = $this->getInstansiatedDeck($request, 'deck_2');
        $cardDealer = new CardDealer();
        $hands = $cardDealer->deal($deck, $players, $cards);
        return new JsonResponse($hands);
    }

    public function resetDeck(Request $request): JsonResponse
    {
        $session = $request->getSession();
        $session->clear();

        return new JsonResponse(['message' => 'Deck reset!']);
    }

}
