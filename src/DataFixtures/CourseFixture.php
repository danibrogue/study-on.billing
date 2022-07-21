<?php

namespace App\DataFixtures;

use App\Entity\Course;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CourseFixture extends Fixture
{

    /**
     * @inheritDoc
     */
    public function load(ObjectManager $manager)
    {
        $courses = [
            [
                'code' => 'LRAL',
                'type' => 1,
                'price' => 0
            ],
            [
                'code' => 'DRSR',
                'type' => 2,
                'price' => 500
            ],
            [
                'code' => 'MRSC',
                'type' => 2,
                'price' => 800
            ],
            [
                'code' => 'DTSR',
                'type' => 3,
                'price' => 5000
            ]
        ];

        foreach ($courses as $courseData)
        {
            $course = new Course();
            $course->setType($courseData['type']);
            $course->setCode($courseData['code']);
            $course->setPrice($courseData['price']);

            $manager->persist($course);
        }
        $manager->flush();
    }
}