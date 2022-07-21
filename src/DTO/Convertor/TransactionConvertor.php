<?php

namespace App\DTO\Convertor;

use App\DTO\TransactionDTO;

class TransactionConvertor
{
    public static function toDTO(array $transactions): array
    {
        $transactionsDTO = [];

        foreach($transactions as $transaction)
        {
            $DTO = new TransactionDTO();
            $DTO->type = $transaction->getType();
            $DTO->amount = $transaction->getAmount();
            $DTO->courseCode = $transaction->getCourse()->getCode();
            $DTO->createdAt = $transaction->getCreateTime();
            $DTO->id = $transaction->getId();

            $transactionsDTO[] = $DTO;
        }

        return $transactionsDTO;
    }
}