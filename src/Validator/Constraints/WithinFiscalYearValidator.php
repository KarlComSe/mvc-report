<?php

namespace App\Validator\Constraints;

use App\Entity\JournalEntry;
use DateTimeImmutable;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class WithinFiscalYearValidator extends ConstraintValidator
{
    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof WithinFiscalYear) {
            throw new UnexpectedTypeException($constraint, WithinFiscalYear::class);
        }

        if (!$value instanceof JournalEntry) {
            throw new UnexpectedValueException($value, JournalEntry::class);
        }

        $journal = $value->getJournal();

        // it can be argued that this silent "passing" is not correct behavior.
        if (!$journal) {
            return;
        }

        $entryDate = $value->getDate();

        if (!$entryDate) {
            return;
        }

        $firstDay = $journal->getFirstDay();
        $lastDay = $journal->getLastDay();

        if (!$firstDay || !$lastDay) {
            return;
        }
        $entryDateNormalized = DateTimeImmutable::createFromInterface($entryDate);
        $firstDayNormalized = DateTimeImmutable::createFromInterface($firstDay);
        $lastDayNormalized = DateTimeImmutable::createFromInterface($lastDay);


        $entryDateOnly = $entryDateNormalized->setTime(0, 0, 0);
        $firstDayOnly = $firstDayNormalized->setTime(0, 0, 0);
        $lastDayOnly = $lastDayNormalized->setTime(23, 59, 59);

        if ($entryDateOnly < $firstDayOnly || $entryDateOnly > $lastDayOnly) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ date }}', $entryDate->format('Y-m-d'))
                ->setParameter('{{ firstDay }}', $firstDay->format('Y-m-d'))
                ->setParameter('{{ lastDay }}', $lastDay->format('Y-m-d'))
                ->atPath('date')
                ->addViolation();
        }
    }
}
