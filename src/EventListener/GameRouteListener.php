<?php

namespace App\EventListener;

use App\Model\AiPlayerDecorator;
use Symfony\Component\HttpKernel\Event\RequestEvent;

use App\Model\Game;
use Random\Randomizer;
use App\Model\HumanPlayer;
use App\Model\BankPlayer;
use App\Model\FrenchSuitedDeck;

class GameRouteListener
{
    public function onKernelRequest(RequestEvent $event)
    {

        $request = $event->getRequest();
        $session = $request->getSession();

        $attributes = $request->attributes->get('_route_params');

        if ($attributes && key_exists('game_needed', $attributes)) {
            if (!$session->has('game')) {
                $flashes = $session->getFlashBag();
                $flashes->add(
                    'notice',
                    'No game existed, had to create game!'
                );
                $frenchDeck = FrenchSuitedDeck::create(new Randomizer());
                $frenchDeck->shuffle();
                $game = new Game(
                    [
                        new HumanPlayer(),
                        new BankPlayer(),
                    ],
                    $frenchDeck
                );

                $session->set('game', $game->getGameState());
            }
        }
    }
}
