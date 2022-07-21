<?php

namespace App\DTO;

use App\Entity\Course;
use JMS\Serializer\Annotation as Serializer;

class CourseDTO
{
    /**
     * @Serializer\Type("string")
     */
    public $code;

    /**
     * @Serializer\Type("string")
     */
    public $type;

    /**
     * @Serializer\Type("float")
     */
    public $price;

}