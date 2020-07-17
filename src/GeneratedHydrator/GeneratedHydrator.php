<?php

declare(strict_types=1);

namespace GeneratedHydrator;

use Laminas\Hydrator\HydratorInterface;

/**
 * @internal this interface serves for type inference only, and is not supposed to be used nor referenced directly.
 *
 * @psalm-template HydratedObject of object
 */
interface GeneratedHydrator extends HydratorInterface
{
    /**
     * @internal Generated hydrators have a zero-argument constructor: this is mostly used for internal usage, and not to
     *           be relied upon in third-party packages
     */
    public function __construct();

    /**
     * {@inheritDoc}
     *
     * @psalm-param HydratedObject $object
     * @psalm-return HydratedObject
     */
    public function hydrate(array $data, $object);

    /**
     * {@inheritDoc}
     *
     * @psalm-param HydratedObject $object
     */
    public function extract($object);
}
