<?php

namespace App\Controller;

# Generic use statements
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Model\CardHand;
use App\Model\DeckShuffler;
use App\Model\FrenchSuitedDeck;
use App\Observer\SessionSavingObserver;
use Random\Randomizer;

class CardController extends AbstractController
{
    public function getInstansiatedDeck(Request $request, string $deckName): FrenchSuitedDeck
    {
        $sessionObserver = new SessionSavingObserver($request, $deckName);
        $observers = [
            $sessionObserver
        ];
        return FrenchSuitedDeck::createFromSession($request->getSession()->get($deckName), $observers);
    }

    #[Route('/card', name: 'app_card', defaults: ['deck_needed' => true ])]
    public function index(): Response
    {
        return $this->render('card/index.html.twig');
    }

    #[Route('/session')]
    #[Route('/card/session_view', name: 'app_session_view')]
    public function sessionView(Request $request): Response
    {
        $session = $request->getSession();
        return $this->render('card/session_view.html.twig', ['session' => $session]);
    }

    #[Route('/session/delete')]
    #[Route('/card/session_destroy', name: 'app_session_destroy')]
    public function sessionDestroy(Request $request): Response
    {
        $session = $request->getSession();
        $session->clear();
        $this->addFlash(
            'notice',
            'Session data was deleted!'
        );

        return $this->redirect($this->generateUrl('app_card'));
    }

    #[Route('/card/deck', name: 'app_deck', defaults: ['deck_needed' => true ])]
    public function deck(Request $request): Response
    {
        $deck = $this->getInstansiatedDeck($request, 'deck_2');
        $deck->sort();
        return $this->render('card/cards.html.twig', ['cards' => $deck->getDeck()]);
    }

    #[Route('/card/deck/shuffle', name: 'app_deck_shuffle', defaults: ['deck_needed' => true ])]
    public function deckShuffle(Request $request): Response
    {
        $deck = $this->getInstansiatedDeck($request, 'deck_2');
        $shuffler = new DeckShuffler(new Randomizer());
        $shuffler->shuffle($deck);
        return $this->render('card/cards.html.twig', ['cards' => $deck->getDeck()]);
    }

    #[Route('/card/deck/draw/{number}', name: 'app_deck_draw', defaults: ['deck_needed' => true ])]
    public function deckDraw(Request $request, int $number = 1): Response
    {
        $deck = $this->getInstansiatedDeck($request, 'deck_2');

        return $this->render('card/cards.html.twig', ['cards' => $deck->cardCollection->drawCards($number)]);
    }

    #[Route('/card/deck/deal/{players}/{cards}', name: 'app_deck_deal', defaults: ['deck_needed' => true ])]
    public function deckDeal(Request $request, int $players, int $cards): Response
    {
        $deck = $this->getInstansiatedDeck($request, 'deck_2');

        $hands = [];
        for ($i = 0; $i < $players; $i++) {
            $hands[] = new CardHand();
        }

        for ($i = 0; $i < $cards; $i++) {
            foreach ($hands as $hand) {
                $cardArray = $deck->cardCollection->drawCards(1);
                foreach ($cardArray as $card) {
                    $hand->addCard($card);
                }
            }
        }

        return $this->render('card/hands.html.twig', ['hands' => $hands, 'deck' => $deck]);
    }
}
