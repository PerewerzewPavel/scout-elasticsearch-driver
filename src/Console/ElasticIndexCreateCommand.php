<?php

namespace ScoutElastic\Console;

use App\Models\Clinic;
use Illuminate\Console\Command;
use ScoutElastic\Console\Features\RequiresIndexConfiguratorArgument;
use ScoutElastic\Facades\ElasticClient;
use ScoutElastic\Migratable;
use ScoutElastic\Payloads\IndexPayload;
use ScoutElastic\Payloads\RawPayload;

class ElasticIndexCreateCommand extends Command
{
    use RequiresIndexConfiguratorArgument;

    /**
     * @var string
     */
    protected $name = 'elastic:create-index';

    /**
     * @var string
     */
    protected $description = 'Create an Elasticsearch index';

    protected function createIndex()
    {
        $configurator = $this->getIndexConfigurator();

        if (! empty($configurator->getUsedModel())) {

            foreach ($configurator->getUsedModel() as $model) {
                $configurator->setName(call_user_func([(new $model()), 'getIndicesName']));
                if (! $this->isExists($configurator->getName())) {
                    $payload = (new IndexPayload($configurator))->setIfNotEmpty('body.settings', $configurator->getSettings())->setIfNotEmpty('body.mappings._default_', $configurator->getDefaultMapping())->get();

                    ElasticClient::indices()->create($payload);

                    $this->info(sprintf('The %s index was created!', $configurator->getName()));
                } else {
                    $this->info(sprintf('The %s index already exists!', $configurator->getName()));
                }
            }
        } else {
            if (! $this->isExists($configurator->getName())) {
                $payload = (new IndexPayload($configurator))->setIfNotEmpty('body.settings', $configurator->getSettings())->setIfNotEmpty('body.mappings._default_', $configurator->getDefaultMapping())->get();

                ElasticClient::indices()->create($payload);

                $this->info(sprintf('The %s index was created!', $configurator->getName()));
            } else {
                $this->info(sprintf('The %s index already exists!', $configurator->getName()));
            }
        }
    }

    protected function isExists($name)
    {
        $payload = (new RawPayload())->set('index', $name)->get();

        return ElasticClient::indices()->exists($payload);
    }

    protected function createWriteAlias()
    {
        $configurator = $this->getIndexConfigurator();

        if (! in_array(Migratable::class, class_uses_recursive($configurator))) {
            return;
        }

        $payload = (new IndexPayload($configurator))->set('name', $configurator->getWriteAlias())->get();

        ElasticClient::indices()->putAlias($payload);

        $this->info(sprintf('The %s alias for the %s index was created!', $configurator->getWriteAlias(), $configurator->getName()));
    }

    public function handle()
    {
        $this->createIndex();

        $this->createWriteAlias();
    }
}