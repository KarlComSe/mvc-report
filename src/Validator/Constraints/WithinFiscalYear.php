<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class WithinFiscalYear extends Constraint
{
    public string $message = 'Datumet {{ date }} ligger utanför räkenskapsåret ({{ firstDay }} - {{ lastDay }}).';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
