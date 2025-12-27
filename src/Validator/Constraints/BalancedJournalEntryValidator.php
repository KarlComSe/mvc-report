<?php

namespace App\Validator\Constraints;

use App\Entity\JournalEntry;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class BalancedJournalEntryValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof BalancedJournalEntry) {
            throw new UnexpectedTypeException($constraint, BalancedJournalEntry::class);
        }

        if (!$value instanceof JournalEntry) {
            throw new UnexpectedValueException($value, JournalEntry::class);
        }

        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($value->getJournalLineItems() as $lineItem) {
            $totalDebit += $lineItem->getDebitAmount() ?? 0;
            $totalCredit += $lineItem->getCreditAmount() ?? 0;
        }

        $difference = $totalDebit - $totalCredit;

        if ($difference != 0) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ debit }}', number_format($totalDebit, 2))
                ->setParameter('{{ credit }}', number_format($totalCredit, 2))
                ->addViolation();
        }
    }
}
