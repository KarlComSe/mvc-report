<?php

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\RequestEvent;

use App\Model\Game;
use Random\Randomizer;

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
                $game = new Game();
                $session->set('game', $game->getGameState());
            }
        }
    }
}
