<?php

declare(strict_types=1);

namespace App\Mapper;

use App\Entity\AdEntity;
use App\Entity\Media;
use App\Entity\MediaEntity;
use App\Repository\MediaRepository;
use Symfonycasts\MicroMapper\AsMapper;
use Symfonycasts\MicroMapper\MapperInterface;
use Symfonycasts\MicroMapper\MicroMapperInterface;

#[AsMapper(from: Media::class, to: MediaEntity::class)]
class MediaApiToEntityMapper implements MapperInterface
{
    public function __construct(
        private MediaRepository $mediaRepository,
        private MicroMapperInterface $microMapper,
    ) {
    }

    /**
     * @param array<string, mixed> $context
     *
     * @return MediaEntity
     */
    public function load(object $from, string $toClass, array $context): object
    {
        assert($from instanceof Media);

        $media = isset($from->id) ? $this->mediaRepository->find($from->id) : new MediaEntity();

        if (!$media) {
            throw new \Exception('Media not found');
        }

        return $media;
    }

    /**
     * @param array<string, mixed> $context
     * @param MediaEntity          $to
     *
     * @return MediaEntity
     */
    public function populate(object $from, object $to, array $context): object
    {
        assert($from instanceof Media);
        assert($to instanceof MediaEntity);

        $to
            ->setPath($from->path)
            ->setAd($this->microMapper->map(
                $from->ad,
                AdEntity::class,
                [MicroMapperInterface::MAX_DEPTH => 1]),
            )
        ;

        if (isset($from->id)) {
            $to->setId($from->id);
        }

        return $to;
    }
}
