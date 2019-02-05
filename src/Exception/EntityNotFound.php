<?php

declare(strict_types=1);

namespace KunicMarko\SonataAutoConfigureBundle\Exception;

/**
 * @author Marko Kunic <kunicmarko20@gmail.com>
 */
final class EntityNotFound extends \RuntimeException implements SonataAutoConfigureExceptionInterface
{
    public function __construct(string $name, array $namespaces)
    {
        parent::__construct(sprintf(
            'Entity "%s" not found, looked in "%s" namespaces.',
            $name,
            \implode(', ', \array_column($namespaces, 'namespace'))
        ));
    }
}
