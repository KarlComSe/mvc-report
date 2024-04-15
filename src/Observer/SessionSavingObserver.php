<?php

namespace App\Observer;

use Symfony\Component\HttpFoundation\Request;
use SplObserver;

class SessionSavingObserver implements SplObserver
{
    public function __construct(private Request $request, private string $key)
    {
    }

    public function update($data)
    {
        $session = $this->request->getSession();
        // echo '<pre>';
        // echo 'CALL CALL';
        // echo gettype($data);
        // var_dump($session->get($this->key));
        // echo '</pre>';
        $session->set($this->key, $data);
    }
}
