<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Video;
use App\Enum\VideoStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class VideoFixtures extends Fixture
{
    public const string REF_CITY_TOUR  = 'video_city-tour';
    public const string REF_UNDERWATER = 'video_underwater';
    public const string REF_MOUNTAIN   = 'video_mountain';

    private const array VIDEOS = [
        [self::REF_CITY_TOUR,  'Virtual City Tour',  300, 'h264'],
        [self::REF_UNDERWATER, 'Underwater World',   420, 'h264'],
        [self::REF_MOUNTAIN,   'Mountain Adventure', 600, 'hevc'],
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::VIDEOS as [$ref, $title, $duration, $codec]) {
            $video = new Video();
            $video->setTitle($title);
            $video->setDurationSec($duration);
            $video->setCodec($codec);
            $video->setStatus(VideoStatus::Ready);
            $video->setHlsPath('hls/playlist.m3u8');
            $video->setPreviewImagePath('preview.jpg');
            $video->setIsActive(true);

            $manager->persist($video);
            $this->addReference($ref, $video);
        }

        $manager->flush();
    }
}
