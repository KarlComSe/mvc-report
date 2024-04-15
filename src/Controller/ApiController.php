<?php

namespace App\Controller;

use App\Service\QuoteService;
use App\Service\DeckService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

class ApiController extends AbstractController
{
    public function __construct(protected QuoteService $quoteService, protected DeckService $deckService)
    {
    }

    #[Route('/api', name: 'app_api')]
    public function index()
    {

        $data = [
            'api' => [
                '/api/quote' => 'Provides paraphrased quotes, written by chatGPT, from a fictional character named Assad. The quotes are based on a book series called Department Q by Jussi Adler-Olsen.',
                '/api/number' => 'Provides a random number between 0 and 100.',
                '/api/deck' => 'Provides a French-suited deck of cards, sorted by suit and value.',
                '/api/deck/shuffle' => 'Shuffles the French-suited deck of cards.',
                '/api/deck/draw' => 'Draws one card from the French-suited deck of cards.',
                '/api/deck/draw/{number}' => 'Draws a specified number of cards from the French-suited deck of cards.',
                '/api/deck/deal/{players}/{cards}' => 'Deals a specified number of cards to a specified number of players from the French-suited deck of cards.',
                '/api/deck/reset' => 'Resets the French-suited deck of cards.',
            ],
        ];
        return $this->render('api/index.html.twig', $data);
    }

    #[Route('/api/quote', name: 'app_quotes')]
    #[Route('/api/quote', name: 'api_quotes')]
    public function assadsQuotes()
    {
        $quote = $this->quoteService->getAssadsQuote();
        return $quote;
    }

    #[Route('/api/deck', name: 'api_deck', defaults: ['deck_needed' => true ])]
    public function deck(Request $request)
    {
        $deck = $this->deckService->getDeck($request);
        return $deck;
    }

    #[Route('/api/deck/shuffle', name: 'api_deck_shuffle', defaults: ['deck_needed' => true ])]
    public function shuffle(Request $request)
    {
        $deck = $this->deckService->shuffleDeck($request);
        return $deck;
    }

    #[Route('/api/deck/draw', name: 'api_deck_draw', defaults: ['deck_needed' => true ])]
    public function draw(Request $request)
    {
        $deck = $this->deckService->drawCard($request);
        return $deck;
    }

    #[Route('/api/deck/draw/{number}', name: 'api_deck_draw_number', defaults: ['deck_needed' => true ])]
    public function drawNumber(int $number, Request $request)
    {
        $deck = $this->deckService->drawCards($number, $request);
        return $deck;
    }

    #[Route('/api/deck/deal/{players}/{cards}', name: 'api_deck_deal', defaults: ['deck_needed' => true ])]
    public function deal(int $players, int $cards, Request $request)
    {
        $deck = $this->deckService->dealCards($players, $cards, $request);
        return $deck;
    }

    #[Route('/api/deck/reset', name: 'api_deck_reset', defaults: ['deck_needed' => true ])]
    public function reset(Request $request)
    {
        $deck = $this->deckService->resetDeck($request);
        return $deck;
    }


    // Bygg vidare på din landningssida api/ som visar en webbsida med en sammanställning av alla JSON routes som din webbplats erbjuder. Varje route skall ha en förklaring vad den gör.
    // Skapa en kontroller i Symfony där du kan skapa routes för ett JSON API för denna delen av uppgiften.
    // Skapa en route GET api/deck som returnerar en JSON struktur med hela kortleken sorterad per färg och värde.
    // Skapa en route POST api/deck/shuffle som blandar kortleken och därefter returnerar en JSON struktur med kortleken. Den blandade kortleken sparas i sessionen.
    // Skapa route POST api/deck/draw och POST api/deck/draw/:number som drar 1 eller :number kort från kortleken och visar upp dem i en JSON struktur samt antalet kort som är kvar i kortleken. Kortleken sparas i sessionen så om man anropar dem flera gånger så minskas antalet kort i kortleken.
    // [OPTIONELLT] Skapa en route POST api/deck/deal/:players/:cards som delar ut ett antal :cards från kortleken till ett antal :players och visar upp de korten som respektive spelare har fått i en JSON struktur. Visa även antalet kort som är kvar i kortleken.

}
