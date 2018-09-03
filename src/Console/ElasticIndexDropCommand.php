<?php

namespace ScoutElastic\Console;

use Illuminate\Console\Command;
use ScoutElastic\Console\Features\RequiresIndexConfiguratorArgument;
use ScoutElastic\Facades\ElasticClient;
use ScoutElastic\Payloads\IndexPayload;
use ScoutElastic\Payloads\RawPayload;

class ElasticIndexDropCommand extends Command
{
    use RequiresIndexConfiguratorArgument;

    /**
     * @var string
     */
    protected $name = 'elastic:drop-index';

    /**
     * @var string
     */
    protected $description = 'Drop an Elasticsearch index';

    public function handle()
    {
        $configurator = $this->getIndexConfigurator();

        if (! empty($configurator->getUsedModel())) {

            foreach ($configurator->getUsedModel() as $model) {
                $configurator->setName(call_user_func([(new $model()), 'getIndicesName']));
                if ( $this->isExists($configurator->getName())) {
                    $payload = (new IndexPayload($configurator))->get();

                    ElasticClient::indices()->delete($payload);

                    $this->info(sprintf('The index %s was deleted!', $configurator->getName()));
                } else {
                    $this->info(sprintf('The %s index already deleted!', $configurator->getName()));
                }
            }
        } else {

            $payload = (new IndexPayload($configurator))->get();

            ElasticClient::indices()->delete($payload);

            $this->info(sprintf('The index %s was deleted!', $configurator->getName()));
        }
    }

    protected function isExists($name)
    {
        $payload = (new RawPayload())->set('index', $name)->get();

        return ElasticClient::indices()->exists($payload);
    }
}