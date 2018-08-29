<?php

namespace ScoutElastic;

use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable as ScoutSearchable;
use ScoutElastic\Builders\FilterBuilder;
use ScoutElastic\Builders\SearchBuilder;
use \Exception;
use Illuminate\Support\Str;

trait Searchable
{
    use ScoutSearchable {
        ScoutSearchable::bootSearchable as bootScoutSearchable;
    }

    /**
     * @var Highlight|null
     */
    private $highlight = null;

    /**
     * @var array|null
     */
    protected $suggestion = null;

    /**
     * @var string|null
     */
    protected $indicesName = null;

    /**
     * @var bool
     */
    private static $isSearchableTraitBooted = false;

    public static function bootSearchable()
    {
        if (self::$isSearchableTraitBooted) {
            return;
        }

        self::bootScoutSearchable();

        self::$isSearchableTraitBooted = true;
    }

    /**
     * @return IndexConfigurator
     * @throws Exception
     */
    public function getIndexConfigurator()
    {
        static $indexConfigurator;

        if (!$indexConfigurator) {
            if (!isset($this->indexConfigurator) || empty($this->indexConfigurator)) {
                throw new Exception(sprintf(
                    'An index configurator for the %s model is not specified.',
                    __CLASS__
                ));
            }

            $indexConfiguratorClass = $this->indexConfigurator;
            $indexConfigurator = new $indexConfiguratorClass;
            $indexConfigurator->name = $this->getIndicesName() ?? $indexConfigurator->getName();
        }

        return $indexConfigurator;
    }

    private function getIndicesName(){
        return config('scout.prefix') . Str::snake($this->indicesName);
    }

    public function getSuggestRules()
    {
        return isset($this->suggestRules) && count($this->suggestRules) > 0 ?
            $this->suggestRules : [SuggestRule::class];
    }

    /**
     * @return array
     */
    public function getMapping()
    {
        $mapping = $this->mapping ?? [];

        if ($this->usesSoftDelete() && config('scout.soft_delete', false)) {
            array_set($mapping, 'properties.__soft_deleted', ['type' => 'integer']);
        }

        return $mapping;
    }

    /**
     * @return array
     */
    public function getSearchRules()
    {
        return isset($this->searchRules) && count($this->searchRules) > 0 ?
            $this->searchRules : [SearchRule::class];
    }

    /**
     * @param $query
     * @param null $callback
     * @return FilterBuilder|SearchBuilder
     */
    public static function search($query, $callback = null)
    {
        $softDelete = config('scout.soft_delete', false);

        if ($query == '*') {
            return new FilterBuilder(new static, $callback, $softDelete);
        } else {
            return new SearchBuilder(new static, $query, $callback, $softDelete);
        }
    }

    /**
     * @param array $query
     * @return array
     */
    public static function searchRaw(array $query)
    {
        $model = new static();

        return $model->searchableUsing()
            ->searchRaw($model, $query);
    }

    /**
     * @return bool
     */
    public function usesSoftDelete()
    {
        return in_array(SoftDeletes::class, class_uses_recursive($this));
    }

    /**
     * @param Highlight $value
     */
    public function setHighlightAttribute(Highlight $value)
    {
        $this->highlight = $value;
    }

    /**
     * @return Highlight|null
     */
    public function getHighlightAttribute()
    {
        return $this->highlight;
    }

    /**
     * @param Highlight $value
     */
    public function setSuggestAttribute(Suggest $value)
    {
        $this->suggest = $value;
    }

    /**
     * @return Highlight|null
     */
    public function getSuggestAttribute()
    {
        return $this->suggest;
    }
}
