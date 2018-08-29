<?php

namespace ScoutElastic;

use Illuminate\Support\Str;

abstract class IndexConfigurator
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $settings = [];

    /**
     * @var array
     */
    protected $usedModel = [];

    /**
     * @var array
     */
    protected $defaultMapping = [];

    /**
     * @return string
     */
    public function getName()
    {
        $name = $this->name ?? Str::snake(str_replace('IndexConfigurator', '', class_basename($this)));
        return config('scout.prefix') . $name;
    }

    /**
     * @return array
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * @deprecated
     */
    public function getDefaultMapping()
    {
        return $this->defaultMapping;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return array
     */
    public function getUsedModel(): array
    {
        return $this->usedModel;
    }
}