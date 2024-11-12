<?php

declare(strict_types=1);

namespace App\State\Provider;

use ApiPlatform\Doctrine\Orm\Paginator;
use ApiPlatform\Doctrine\Orm\State\CollectionProvider;
use ApiPlatform\Doctrine\Orm\State\ItemProvider;
use ApiPlatform\Metadata\Exception\OperationNotFoundException;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\TraversablePaginator;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\Ad;
use App\Entity\AdEntity;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Uid\Uuid;
use Symfonycasts\MicroMapper\MicroMapperInterface;

/**
 * @implements ProviderInterface<Ad>
 */
class AdStateProvider implements ProviderInterface
{
    public function __construct(
        private MicroMapperInterface $microMapper,
        private CollectionProvider $doctrineCollectionProvider,
        private ItemProvider $doctrineItemProvider,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        return match (true) {
            $operation instanceof Get => $this->handleRead($operation, $uriVariables, $context),
            $operation instanceof GetCollection => $this->handleReadCollection($operation, $uriVariables, $context),
            default => throw new OperationNotFoundException(),
        };
    }

    /**
     * @param array<string, mixed>                                                   $uriVariables
     * @param array<string, mixed>|array{request?: Request, resource_class?: string} $context
     */
    private function handleRead(Operation $operation, array $uriVariables = [], array $context = []): Ad
    {
        if (!\array_key_exists('id', $uriVariables) || !$uriVariables['id'] instanceof Uuid) {
            throw new BadRequestHttpException();
        }

        $ads = $this->doctrineItemProvider->provide($operation->withClass(AdEntity::class), $uriVariables, $context);

        if (!$ads instanceof AdEntity) {
            throw new NotFoundHttpException();
        }

        return $this->microMapper->map($ads, Ad::class, [MicroMapperInterface::MAX_DEPTH => 1]);
    }

    /**
     * @param array<string, mixed>                                                   $uriVariables
     * @param array<string, mixed>|array{request?: Request, resource_class?: string} $context
     */
    private function handleReadCollection(Operation $operation, array $uriVariables = [], array $context = []): TraversablePaginator
    {
        $ads = $this->doctrineCollectionProvider->provide($operation->withClass(AdEntity::class), $uriVariables, $context);

        assert($ads instanceof Paginator);

        return new TraversablePaginator(
            new \ArrayIterator($this->microMapper->mapMultiple($ads, Ad::class, [MicroMapperInterface::MAX_DEPTH => 1])),
            $ads->getCurrentPage(),
            $ads->getItemsPerPage(),
            $ads->getTotalItems(),
        );
    }
}
