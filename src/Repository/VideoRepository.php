<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Video;
use App\Entity\VrDevice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
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
    public function findForDevice(VrDevice $device): array
    {
        /** @var Video[] $results */
        $results = $this->createQueryBuilder('v')
            ->join('v.vrDevices', 'd')
            ->where('d.id = :deviceId')
            ->andWhere('v.isActive = true')
            ->setParameter('deviceId', $device->getId(), 'uuid')
            ->orderBy('v.title', 'ASC')
            ->getQuery()
            ->getResult();

        return $results;
    }
}
