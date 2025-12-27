<?php

namespace App\Tests\Validator;

use App\Entity\Journal;
use App\Entity\JournalEntry;
use App\Validator\Constraints\WithinFiscalYear;
use App\Validator\Constraints\WithinFiscalYearValidator;
use DateTimeImmutable;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

/**
 * @extends ConstraintValidatorTestCase<WithinFiscalYearValidator>
 */
class WithinFiscalYearValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator(): WithinFiscalYearValidator
    {
        return new WithinFiscalYearValidator();
    }

    public function testDateWithinFiscalYearPassesValidation(): void
    {
        $journal = $this->createMock(Journal::class);
        $journal->method('getFirstDay')->willReturn(new DateTimeImmutable('2025-01-01'));
        $journal->method('getLastDay')->willReturn(new DateTimeImmutable('2025-12-31'));

        $journalEntry = $this->createMock(JournalEntry::class);
        $journalEntry->method('getJournal')->willReturn($journal);
        $journalEntry->method('getDate')->willReturn(new DateTimeImmutable('2025-06-15'));

        $this->validator->validate($journalEntry, new WithinFiscalYear());

        $this->assertNoViolation();
    }

    public function testDateBeforeFiscalYearFailsValidation(): void
    {
        $journal = $this->createMock(Journal::class);
        $journal->method('getFirstDay')->willReturn(new DateTimeImmutable('2025-01-01'));
        $journal->method('getLastDay')->willReturn(new DateTimeImmutable('2025-12-31'));

        $journalEntry = $this->createMock(JournalEntry::class);
        $journalEntry->method('getJournal')->willReturn($journal);
        $journalEntry->method('getDate')->willReturn(new DateTimeImmutable('2024-12-31'));

        $this->validator->validate($journalEntry, new WithinFiscalYear());

        $this->assertCount(1, $this->context->getViolations());
    }

    public function testNoLastDayFailsValidation(): void
    {
        $journal = $this->createMock(Journal::class);
        $journal->method('getFirstDay')->willReturn(new DateTimeImmutable('2025-01-01'));

        $journalEntry = $this->createMock(JournalEntry::class);
        $journalEntry->method('getJournal')->willReturn($journal);
        $journalEntry->method('getDate')->willReturn(new DateTimeImmutable('2024-12-31'));

        $this->validator->validate($journalEntry, new WithinFiscalYear());

        $this->assertNoViolation();
    }

    public function testDateAfterFiscalYearFailsValidation(): void
    {
        $journal = $this->createMock(Journal::class);
        $journal->method('getFirstDay')->willReturn(new DateTimeImmutable('2025-01-01'));
        $journal->method('getLastDay')->willReturn(new DateTimeImmutable('2025-12-31'));

        $journalEntry = $this->createMock(JournalEntry::class);
        $journalEntry->method('getJournal')->willReturn($journal);
        $journalEntry->method('getDate')->willReturn(new DateTimeImmutable('2026-01-01'));

        $this->validator->validate($journalEntry, new WithinFiscalYear());

        $this->assertCount(1, $this->context->getViolations());
    }

    public function testFirstDayOfFiscalYearPassesValidation(): void
    {
        $journal = $this->createMock(Journal::class);
        $journal->method('getFirstDay')->willReturn(new DateTimeImmutable('2025-01-01'));
        $journal->method('getLastDay')->willReturn(new DateTimeImmutable('2025-12-31'));

        $journalEntry = $this->createMock(JournalEntry::class);
        $journalEntry->method('getJournal')->willReturn($journal);
        $journalEntry->method('getDate')->willReturn(new DateTimeImmutable('2025-01-01'));

        $this->validator->validate($journalEntry, new WithinFiscalYear());

        $this->assertNoViolation();
    }

    public function testLastDayOfFiscalYearPassesValidation(): void
    {
        $journal = $this->createMock(Journal::class);
        $journal->method('getFirstDay')->willReturn(new DateTimeImmutable('2025-01-01'));
        $journal->method('getLastDay')->willReturn(new DateTimeImmutable('2025-12-31'));

        $journalEntry = $this->createMock(JournalEntry::class);
        $journalEntry->method('getJournal')->willReturn($journal);
        $journalEntry->method('getDate')->willReturn(new DateTimeImmutable('2025-12-31'));

        $this->validator->validate($journalEntry, new WithinFiscalYear());

        $this->assertNoViolation();
    }

    public function testNullJournalPassesValidation(): void
    {
        $journalEntry = $this->createMock(JournalEntry::class);
        $journalEntry->method('getJournal')->willReturn(null);

        $this->validator->validate($journalEntry, new WithinFiscalYear());

        $this->assertNoViolation();
    }

    public function testNullEntryDatePassesValidation(): void
    {
        $journal = $this->createMock(Journal::class);
        $journal->method('getFirstDay')->willReturn(new DateTimeImmutable('2025-01-01'));
        $journal->method('getLastDay')->willReturn(new DateTimeImmutable('2025-12-31'));

        $journalEntry = $this->createMock(JournalEntry::class);
        $journalEntry->method('getJournal')->willReturn($journal);
        $journalEntry->method('getDate')->willReturn(null);

        $this->validator->validate($journalEntry, new WithinFiscalYear());

        $this->assertNoViolation();
    }

    public function testExceptions(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->validator->validate("kalle anka", new WithinFiscalYear());
    }

    public function testExceptionsAgain(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $journalEntry = $this->createMock(JournalEntry::class);
        $wrongConstraint = $this->createMock(Constraint::class);
        $this->validator->validate($journalEntry, $wrongConstraint);
    }
}
