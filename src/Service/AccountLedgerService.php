<?php

namespace App\Service;

use App\Entity\Journal;

class AccountLedgerService
{
    /**
     * @return array<int, mixed>
     */
    public function getCompleteAccountLedger(Journal $journal): array
    {
        $journalEntries = $journal->getJournalEntries();

        $allLineItems = [];
        foreach ($journalEntries as $entry) {
            foreach ($entry->getJournalLineItems() as $lineItem) {
                $allLineItems[] = $lineItem;
            }
        }

        $groupedByAccount = [];
        foreach ($allLineItems as $lineItem) {
            $account = $lineItem->getAccount();
            $accountId = $account->getId();

            if (!isset($groupedByAccount[$accountId])) {
                $groupedByAccount[$accountId] = [
                    'account' => $account,
                    'lineItems' => [],
                ];
            }

            $groupedByAccount[$accountId]['lineItems'][] = $lineItem;
        }

        $ledgerData = [];
        foreach ($groupedByAccount as $accountData) {
            $account = $accountData['account'];
            $lineItems = $accountData['lineItems'];

            usort($lineItems, function ($lineItem, $otherLineItem) {
                $dateCompare = $lineItem->getJournalEntry()->getDate() <=> $otherLineItem->getJournalEntry()->getDate();
                if ($dateCompare !== 0) {
                    return $dateCompare;
                }
                return $lineItem->getJournalEntry()->getId() <=> $otherLineItem->getJournalEntry()->getId();
            });

            $runningBalance = 0;
            $totalDebit = 0;
            $totalCredit = 0;
            $entries = [];

            foreach ($lineItems as $lineItem) {
                $debit = $lineItem->getDebitAmount() ?? 0;
                $credit = $lineItem->getCreditAmount() ?? 0;

                $runningBalance += ($debit - $credit);
                $totalDebit += $debit;
                $totalCredit += $credit;

                $entries[] = [
                    'date' => $lineItem->getJournalEntry()->getDate(),
                    'verificationNumber' => $lineItem->getJournalEntry()->getId(),
                    'description' => $lineItem->getJournalEntry()->getTitle(),
                    'debit' => $debit,
                    'credit' => $credit,
                    'balance' => $runningBalance,
                    'journalEntry' => $lineItem->getJournalEntry(),
                    'lineItem' => $lineItem,
                ];
            }

            $ledgerData[] = [
                'account' => $account,
                'accountNumber' => $account->getAccountNumber(),
                'accountName' => $account->getName(),
                'typeName' => $account->getTypeName(),
                'entries' => $entries,
                'summary' => [
                    'totalDebit' => $totalDebit,
                    'totalCredit' => $totalCredit,
                    'finalBalance' => $runningBalance,
                    'entryCount' => count($entries),
                ],
            ];
        }

        usort($ledgerData, function ($account, $anotherAccount) {
            return $account['accountNumber'] <=> $anotherAccount['accountNumber'];
        });

        return $ledgerData;
    }
}
