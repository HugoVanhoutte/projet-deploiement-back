<?php

declare(strict_types=1);

namespace App\State\Processor;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use ApiPlatform\Metadata\Exception\OperationNotFoundException;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\Validator\ValidatorInterface;
use App\ApiResource\Ad;
use App\Entity\AdEntity;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfonycasts\MicroMapper\MicroMapperInterface;

/**
 * @implements ProcessorInterface<Ad, Ad>
 */
class AdStateProcessor implements ProcessorInterface
{
    /**
     * @param ProcessorInterface<AdEntity, AdEntity> $persistProcessor
     */
    public function __construct(
        #[Autowire(service: PersistProcessor::class)]
        private ProcessorInterface $persistProcessor,
        private MicroMapperInterface $microMapper,
        private ValidatorInterface $validator,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        assert($data instanceof Ad);

        return match (true) {
            $operation instanceof Post => $this->handleCreation($data, $operation, $uriVariables, $context),
            default => throw new OperationNotFoundException(),
        };
    }

    /**
     * @param array<string, mixed>                                                                                                                               $uriVariables
     * @param array<string, mixed>&array{request?: Request|\Illuminate\Http\Request, previous_data?: mixed, resource_class?: string|null, original_data?: mixed} $context
     */
    public function handleCreation(Ad $ad, Operation $operation, array $uriVariables = [], array $context = []): Ad
    {
        $entity = $this->microMapper->map($ad, AdEntity::class, [MicroMapperInterface::MAX_DEPTH => 1]);

        $this->validator->validate($entity);

        $this->persistProcessor->process($entity, $operation, $uriVariables, $context);

        $ad->id = $entity->getId();

        return $ad;
    }
}
