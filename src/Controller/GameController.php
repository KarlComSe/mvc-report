<?php

namespace App\Controller;

# Generic use statements
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use Symfony\Component\HttpFoundation\Request;

use App\Model\Card;
use App\Model\CardHand;
use App\Model\GraphicCard;
use App\Model\FrenchSuitedDeck;
use App\Observer\SessionSavingObserver;

use Random\Randomizer;

class GameController extends AbstractController
{
    #[Route('/game', name: 'app_game', defaults: ['deck_needed' => true ])]
    public function index(Request $request)
    {
        return $this->render('game/index.html.twig');
    }

    #[Route('/game/doc', name: 'app_game_doc')]
    public function doc()
    {
        return $this->render('game/doc.html.twig');
    }

    #[Route('/game/play', name: 'app_play_game', defaults: ['deck_needed' => true ])]
    public function play(Request $request)
    {
    }
}
