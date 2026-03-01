<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Video;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class VideoFixtures extends Fixture
{
    public const string REF_CITY_TOUR  = 'video_city-tour';
    public const string REF_UNDERWATER = 'video_underwater';
    public const string REF_MOUNTAIN   = 'video_mountain';

    private const array VIDEOS = [
        [self::REF_CITY_TOUR,  'Virtual City Tour',   'https://example.com/videos/city-tour.mp4',  300],
        [self::REF_UNDERWATER, 'Underwater World',    'https://example.com/videos/underwater.mp4', 420],
        [self::REF_MOUNTAIN,   'Mountain Adventure',  'https://example.com/videos/mountain.mp4',   600],
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::VIDEOS as [$ref, $title, $url, $duration]) {
            $video = new Video();
            $video->setTitle($title);
            $video->setUrl($url);
            $video->setDurationSeconds($duration);
            $video->setIsActive(true);

            $manager->persist($video);
            $this->addReference($ref, $video);
        }

        $manager->flush();
    }
}
