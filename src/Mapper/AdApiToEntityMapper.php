<?php

declare(strict_types=1);

namespace App\Mapper;

use App\ApiResource\Ad;
use App\Entity\AdEntity;
use App\Entity\MediaEntity;
use App\Repository\AdRepository;
use Symfonycasts\MicroMapper\AsMapper;
use Symfonycasts\MicroMapper\MapperInterface;
use Symfonycasts\MicroMapper\MicroMapperInterface;

#[AsMapper(from: Ad::class, to: AdEntity::class)]
class AdApiToEntityMapper implements MapperInterface
{
    public function __construct(
        private AdRepository $adRepository,
        private MicroMapperInterface $microMapper,
    ) {
    }

    /**
     * @param array<string, mixed> $context
     *
     * @return AdEntity
     */
    public function load(object $from, string $toClass, array $context): object
    {
        assert($from instanceof Ad);

        $ad = isset($from->id) ? $this->adRepository->find($from->id) : new AdEntity();

        if (!$ad) {
            throw new \Exception('Ad not found');
        }

        return $ad;
    }

    /**
     * @param array<string, mixed> $context
     * @param AdEntity             $to
     *
     * @return AdEntity
     */
    public function populate(object $from, object $to, array $context): object
    {
        assert($from instanceof Ad);
        assert($to instanceof AdEntity);

        $to
            ->setTitle($from->title)
            ->setMedias($this->microMapper->mapMultiple(
                $from->medias,
                MediaEntity::class,
                [MicroMapperInterface::MAX_DEPTH => 1],
            ))
        ;

        if (isset($from->id)) {
            $to->setId($from->id);
        }

        return $to;
    }
}
