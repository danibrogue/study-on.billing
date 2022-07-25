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
            $DTO->createdAt = $transaction->getCreateTime();
            $DTO->id = $transaction->getId();
            $DTO->courseCode = $DTO->type === 2 ? $transaction->getCourse()->getCode() : null;
            $DTO->expiresAt = is_null($transaction->getExpireTime()) ? null : $transaction->getExpireTime();

            $transactionsDTO[] = $DTO;
        }

        return $transactionsDTO;
    }
}