<?php

declare(strict_types=1);

namespace App\State\Processor;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use ApiPlatform\Metadata\Exception\OperationNotFoundException;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\Validator\ValidatorInterface;
use App\Entity\Media;
use App\Entity\MediaEntity;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfonycasts\MicroMapper\MicroMapperInterface;
use Vich\UploaderBundle\Handler\UploadHandler;

/**
 * @implements ProcessorInterface<Media, Media>
 */
class MediaStateProcessor implements ProcessorInterface
{
    /**
     * @param ProcessorInterface<MediaEntity, MediaEntity> $persistProcessor
     */
    public function __construct(
        #[Autowire(service: PersistProcessor::class)]
        private ProcessorInterface $persistProcessor,
        private MicroMapperInterface $microMapper,
        private ValidatorInterface $validator,
        private UploadHandler $uploadHandler,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        assert($data instanceof Media);

        return match (true) {
            $operation instanceof Post => $this->handleCreation($data, $operation, $uriVariables, $context),
            default => throw new OperationNotFoundException(),
        };
    }

    /**
     * @param array<string, mixed>                                                                                                                               $uriVariables
     * @param array<string, mixed>&array{request?: Request|\Illuminate\Http\Request, previous_data?: mixed, resource_class?: string|null, original_data?: mixed} $context
     */
    public function handleCreation(Media $media, Operation $operation, array $uriVariables = [], array $context = []): Media
    {
        $this->uploadHandler->upload($media, 'file');

        $entity = $this->microMapper->map($media, MediaEntity::class, [MicroMapperInterface::MAX_DEPTH => 1]);

        $this->validator->validate($entity);

        $this->persistProcessor->process($entity, $operation, $uriVariables, $context);

        $media->id = $entity->getId();

        return $media;
    }
}
