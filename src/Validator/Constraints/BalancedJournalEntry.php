<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class BalancedJournalEntry extends Constraint
{
    public string $message = 'Verifikatet är inte balanserat. Debet ({{ debit }}) måste vara lika med Kredit ({{ credit }}).';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
