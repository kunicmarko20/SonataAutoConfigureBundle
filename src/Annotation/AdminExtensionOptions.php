<?php

declare(strict_types=1);

namespace KunicMarko\SonataAutoConfigureBundle\Annotation;

/**
 * @Annotation
 * @Target("CLASS")
 *
 * @author Marco Polichetti <gremo1982@gmail.com>
 */
final class AdminExtensionOptions
{
    /**
     * @var bool
     */
    public $global;

    /**
     * @var string[]
     */
    public $target;

    public function getOptions(): array
    {
        return array_filter(
            [
                'global' => $this->global,
                'target' => $this->target,
            ],
            function ($value) {
                return $value !== null;
            }
        );
    }
}
