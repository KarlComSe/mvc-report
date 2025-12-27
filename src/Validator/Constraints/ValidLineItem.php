<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class ValidLineItem extends Constraint
{
    public string $accountRequired = 'Konto måste väljas.';
    public string $amountRequired = 'Antingen Debet eller Kredit måste fyllas i.';
    public string $bothAmounts = 'Endast ett av Debet eller Kredit får fyllas i, inte båda.';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
