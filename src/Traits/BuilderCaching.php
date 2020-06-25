<?php

namespace GeneaLabs\LaravelModelCaching\Traits;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\MySqlConnection;
use Illuminate\Database\SQLiteConnection;
use Illuminate\Database\SqlServerConnection;

trait BuilderCaching
{
    /**
     * The maximum number of parameters per query.
     *
     * The values are lower than the actual limits
     * to have a margin for polymorphic relationships
     * and other query parameters.
     *
     * @var array
     */
    protected $parameterLimits = [
        MySqlConnection::class => 65000,
        SQLiteConnection::class => 900,
        SqlServerConnection::class => 2000,
    ];

    /**
     * Eager load the relationships for the models.
     *
     * @param array $models
     * @return array
     */
    public function eagerLoadRelations(array $models)
    {
        foreach ($this->parameterLimits as $class => $limit) {
            if ($this->query->getConnection() instanceof $class && count($models) > $limit) {
                foreach (array_chunk($models, $limit) as $chunk) {
                    $this->eagerLoadRelations($chunk);
                }

                return $models;
            }
        }

        return parent::eagerLoadRelations($models);
    }

    public function all($columns = ['*']): Collection
    {
        if (!$this->isCachable()) {
            $this->model->disableModelCaching();
        }

        return $this->model->get($columns);
    }

    public function truncate()
    {
        if ($this->isCachable()) {
            $this->model->flushCache();
        }

        return parent::truncate();
    }
}
