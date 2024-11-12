<?php

declare(strict_types=1);

namespace App\Mapper;

use App\Entity\Media;
use App\Entity\MediaEntity;
use Symfonycasts\MicroMapper\AsMapper;
use Symfonycasts\MicroMapper\MapperInterface;

#[AsMapper(from: MediaEntity::class, to: Media::class)]
class MediaEntityToApiMapper implements MapperInterface
{
    /**
     * @param array<string, mixed>&array{request?: Request, previous_data?: mixed, resource_class?: string, original_data?: mixed} $context
     *
     * @return Media
     */
    public function load(object $from, string $toClass, array $context): object
    {
        assert($from instanceof MediaEntity);

        $dto = new Media();
        $dto->id = $from->getId();

        return $dto;
    }

    /**
     * @param array<string, mixed>&array{request?: Request, previous_data?: mixed, resource_class?: string, original_data?: mixed} $context
     */
    public function populate(object $from, object $to, array $context): object
    {
        assert($from instanceof MediaEntity);
        assert($to instanceof Media);

        $to->id = $from->getId();
        $to->path = $from->getPath();

        return $to;
    }
}
