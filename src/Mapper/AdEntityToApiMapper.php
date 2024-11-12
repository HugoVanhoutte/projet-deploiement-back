<?php

declare(strict_types=1);

namespace App\Mapper;

use App\ApiResource\Ad;
use App\Entity\AdEntity;
use App\Entity\Media;
use Symfonycasts\MicroMapper\AsMapper;
use Symfonycasts\MicroMapper\MapperInterface;
use Symfonycasts\MicroMapper\MicroMapperInterface;

#[AsMapper(from: AdEntity::class, to: Ad::class)]
class AdEntityToApiMapper implements MapperInterface
{
    public function __construct(
        private MicroMapperInterface $microMapper,
    ) {
    }

    /**
     * @param array<string, mixed>&array{request?: Request, previous_data?: mixed, resource_class?: string, original_data?: mixed} $context
     *
     * @return Ad
     */
    public function load(object $from, string $toClass, array $context): object
    {
        assert($from instanceof AdEntity);

        $dto = new Ad();
        $dto->id = $from->getId();

        return $dto;
    }

    /**
     * @param array<string, mixed>&array{request?: Request, previous_data?: mixed, resource_class?: string, original_data?: mixed} $context
     */
    public function populate(object $from, object $to, array $context): object
    {
        assert($from instanceof AdEntity);
        assert($to instanceof Ad);

        $to->id = $from->getId();
        $to->title = $from->getTitle();
        $to->medias = $this->microMapper->mapMultiple(
            $from->getMedias(),
            Media::class,
            [MicroMapperInterface::MAX_DEPTH => 1],
        );

        return $to;
    }
}
