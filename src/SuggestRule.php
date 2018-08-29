<?php

namespace ScoutElastic;

use ScoutElastic\Builders\SearchBuilder;

class SuggestRule
{
    /**
     * @var SearchBuilder
     */
    protected $builder;

    /**
     * @param SearchBuilder $builder
     */
    public function __construct(SearchBuilder $builder)
    {
        $this->builder = $builder;
    }

    /**
     * @return array
     */
    public function buildSuggestPayload()
    {
        return null;
    }
}