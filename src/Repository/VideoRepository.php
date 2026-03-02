<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Video;
use App\Entity\VrDevice;
use App\Enum\VideoStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Video>
 */
class VideoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Video::class);
    }

    /** @return Video[] */
    public function findReadyForDevice(VrDevice $device): array
    {
        $deviceVideos = $this->createQueryBuilder('v')
            ->join('v.vrDevices', 'd')
            ->where('d.id = :deviceId')
            ->andWhere('v.isActive = true')
            ->andWhere('v.status = :status')
            ->setParameter('deviceId', $device->getId(), 'uuid')
            ->setParameter('status', VideoStatus::Ready)
            ->orderBy('v.title', 'ASC')
            ->getQuery()
            ->getResult();

        return $deviceVideos;
    }

    public function findReadyById(string $id): ?Video
    {
        return $this->createQueryBuilder('v')
            ->where('v.id = :id')
            ->andWhere('v.isActive = true')
            ->andWhere('v.status = :status')
            ->setParameter('id', $id, 'uuid')
            ->setParameter('status', VideoStatus::Ready)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
