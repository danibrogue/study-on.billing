<?php

namespace App\DTO;

use JMS\Serializer\Annotation as Serializer;

class TransactionDTO
{
    /**
     * @Serializer\Type("int")
     */
    public $id;

    /**
     * @Serializer\Type("DateTimeImmutable")
     */
    public $createdAt;

    /**
     * @Serializer\Type("string")
     */
    public $type;

    /**
     * @Serializer\Type("string")
     */
    public $courseCode;

    /**
     * @Serializer\Type("float")
     */
    public $amount;
}