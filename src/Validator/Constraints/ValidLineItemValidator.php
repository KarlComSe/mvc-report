<?php

namespace App\Validator\Constraints;

use App\Entity\JournalLineItem;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ValidLineItemValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof ValidLineItem) {
            throw new UnexpectedTypeException($constraint, ValidLineItem::class);
        }

        if (!$value instanceof JournalLineItem) {
            throw new UnexpectedTypeException($value, JournalLineItem::class);
        }

        $account = $value->getAccount();
        $debit = $value->getDebitAmount();
        $credit = $value->getCreditAmount();

        // Validering 1: Konto måste finnas
        if (!$account) {
            $this->context->buildViolation($constraint->accountRequired)
                ->atPath('account')
                ->addViolation();
            return;
        }
        $this->validateAmounts($debit, $credit, $constraint);

    }

    /**
     * @param float|null $debit
     * @param float|null $credit
     * @param ValidLineItem $constraint
     * @return void
     */
    public function validateAmounts(?float $debit, ?float $credit, ValidLineItem $constraint): void
    {
        // Validering 2: Minst en av debet eller kredit måste vara ifylld
        $hasDebit = $debit !== null && $debit > 0;
        $hasCredit = $credit !== null && $credit > 0;

        if (!$hasDebit && !$hasCredit) {
            $this->context->buildViolation($constraint->amountRequired)
                ->atPath('debitAmount')
                ->addViolation();
            return;
        }

        // Validering 3: Inte både debet OCH kredit
        if ($hasDebit && $hasCredit) {
            $this->context->buildViolation($constraint->bothAmounts)
                ->atPath('debitAmount')
                ->addViolation();
        }
    }
}
