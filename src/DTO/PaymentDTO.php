<?php

namespace App\DTO;

use JMS\Serializer\Annotation as Serializer;

class PaymentDTO
{
    /**
     * @Serializer\Type("bool")
     */
    public $success;

    /**
     * @Serializer\Type("string")
     */
    public $courseType;

    /**
     * @Serializer\Type("DateTimeImmutable")
     */
    public $expiresAt;

}