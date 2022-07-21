<?php

namespace App\DTO\Convertor;

use App\DTO\CourseDTO;

class CourseConvertor
{
    public static function toDTO(array $courses): array
    {
        $coursesDTO = [];

        foreach ($courses as $course)
        {
            $DTO = new CourseDTO();
            $DTO->code = $course->getCode();
            $DTO->type = $course->getType();
            $DTO->price = $course->getPrice();

            $coursesDTO[] = $DTO;
        }

        return $coursesDTO;
    }
}