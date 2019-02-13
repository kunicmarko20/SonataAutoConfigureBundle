<?php

declare(strict_types=1);

namespace KunicMarko\SonataAutoConfigureBundle\Annotation;

/**
 * @Annotation
 * @Target("CLASS")
 *
 * @author Marko Kunic <kunicmarko20@gmail.com>
 */
final class AdminOptions
{
    /**
     * @var string
     */
    public $label;

    /**
     * @var string
     */
    public $managerType;

    /**
     * @var string
     */
    public $group;

    /**
     * @var bool
     */
    public $showInDashboard;

    /**
     * @var bool
     */
    public $showMosaicButton;

    /**
     * @var bool
     */
    public $keepOpen;

    /**
     * @var bool
     */
    public $onTop;

    /**
     * @var string
     */
    public $icon;

    /**
     * @var string
     */
    public $labelTranslatorStrategy;

    /**
     * @var string
     */
    public $labelCatalogue;

    /**
     * @var string
     */
    public $translationDomain;

    /**
     * @var string
     */
    public $pagerType;

    /**
     * @var string
     */
    public $adminCode;

    /**
     * @var string
     */
    public $entity;

    /**
     * @var string
     */
    public $controller;

    /**
     * @var bool
     */
    public $autowireEntity = true;

    /**
     * @var array<string, string>
     */
    public $templates = [];

    /**
     * @var string[]
     */
    public $children = [];

    public function getOptions(): array
    {
        return \array_filter(
            [
                'manager_type'              => $this->managerType,
                'group'                     => $this->group,
                'label'                     => $this->label,
                'show_in_dashboard'         => $this->showInDashboard,
                'show_mosaic_button'        => $this->showMosaicButton,
                'keep_open'                 => $this->keepOpen,
                'on_top'                    => $this->onTop,
                'icon'                      => $this->icon,
                'label_translator_strategy' => $this->labelTranslatorStrategy,
                'label_catalogue'           => $this->labelCatalogue,
                'pager_type'                => $this->pagerType,
            ],
            static function ($value): bool {
                return $value !== null;
            }
        );
    }
}
