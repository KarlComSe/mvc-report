<?php

namespace App\Controller;

use App\Service\DeckService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class DeckApiController extends AbstractController
{
    public function __construct(protected DeckService $deckService)
    {
    }

    #[Route('/api/deck', name: 'api_deck', defaults: ['deck_needed' => true ])]
    public function deck(Request $request): JsonResponse
    {
        $deck = $this->deckService->getDeck($request);
        return $deck;
    }

    #[Route('/api/deck/shuffle', name: 'api_deck_shuffle', defaults: ['deck_needed' => true ])]
    public function shuffle(Request $request): JsonResponse
    {
        $deck = $this->deckService->shuffleDeck($request);
        return $deck;
    }

    #[Route('/api/deck/draw', name: 'api_deck_draw', defaults: ['deck_needed' => true ])]
    public function draw(Request $request): JsonResponse
    {
        $deck = $this->deckService->drawCard($request);
        return $deck;
    }

    #[Route('/api/deck/draw/{number}', name: 'api_deck_draw_number', defaults: ['deck_needed' => true ])]
    public function drawNumber(int $number, Request $request): JsonResponse
    {
        $deck = $this->deckService->drawCards($number, $request);
        return $deck;
    }

    #[Route('/api/deck/deal/{players}/{cards}', name: 'api_deck_deal', defaults: ['deck_needed' => true ])]
    public function deal(int $players, int $cards, Request $request): JsonResponse
    {
        $deck = $this->deckService->dealCards($players, $cards, $request);
        return $deck;
    }

    #[Route('/api/deck/reset', name: 'api_deck_reset', defaults: ['deck_needed' => true ])]
    public function reset(Request $request): JsonResponse
    {
        $deck = $this->deckService->resetDeck($request);
        return $deck;
    }
}
