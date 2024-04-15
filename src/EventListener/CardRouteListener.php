<?php

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\RequestEvent;

use App\Model\FrenchSuitedDeck;
use Random\Randomizer;

class CardRouteListener
{
    public function onKernelRequest(RequestEvent $event)
    {

        $request = $event->getRequest();
        $session = $request->getSession();

        $attributes = $request->attributes->get('_route_params');

        if ($attributes && key_exists('deck_needed', $attributes)) {
            if (!$session->has('deck_2')) {
                $flashes = $session->getFlashBag();
                $flashes->add(
                    'notice',
                    'No deck existed, had to create deck!'
                );
                $frenchDeck = FrenchSuitedDeck::create(new Randomizer());
                $session->set('deck_2', $frenchDeck->getDeck());
            }
        }
    }
}
