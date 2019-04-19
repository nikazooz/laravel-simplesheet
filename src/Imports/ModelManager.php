<?php

namespace Nikazooz\Simplesheet\Imports;

use Throwable;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Nikazooz\Simplesheet\Concerns\ToModel;
use Nikazooz\Simplesheet\Concerns\SkipsOnError;
use Nikazooz\Simplesheet\Concerns\WithValidation;
use Nikazooz\Simplesheet\Validators\RowValidator;
use Nikazooz\Simplesheet\Exceptions\RowSkippedException;

class ModelManager
{
    /**
     * @var array
     */
    private $rows = [];

    /**
     * @var \Nikazooz\Simplesheet\Validators\RowValidator
     */
    private $validator;

    /**
     * @param  \Nikazooz\Simplesheet\Validators\RowValidator  $validator
     * @return void
     */
    public function __construct(RowValidator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param  int  $row
     * @param  array  $attributes
     * @return void
     */
    public function add(int $row, array $attributes)
    {
        $this->rows[$row] = $attributes;
    }

    /**
     * @param  \Nikazooz\Simplesheet\Concerns\ToModel  $import
     * @param  bool  $massInsert
     * @return void
     */
    public function flush(ToModel $import, bool $massInsert = false)
    {
        if ($import instanceof WithValidation) {
            $this->validateRows($import);
        }

        if ($massInsert) {
            $this->massFlush($import);
        } else {
            $this->singleFlush($import);
        }

        $this->rows = [];
    }

    /**
     * @param  \Nikazooz\Simplesheet\Concerns\ToModel  $import
     * @param  array  $attributes
     * @return \Illuminate\Database\Eloquent\Model[]|\Illuminate\Support\Collection
     */
    public function toModels(ToModel $import, array $attributes): Collection
    {
        $model = $import->model($attributes);

        if (null !== $model) {
            return \is_array($model) ? new Collection($model) : new Collection([$model]);
        }

        return new Collection([]);
    }

    /**
     * @param  \Nikazooz\Simplesheet\Concerns\ToModel  $import
     * @return void
     */
    private function massFlush(ToModel $import)
    {
        $this->rows()->flatMap(function (array $attributes) use ($import) {
            return $this->toModels($import, $attributes);
        })->mapToGroups(function ($model) {
            return [\get_class($model) => $this->prepare($model)->getAttributes()];
        })->each(function (Collection $models, string $model) use ($import) {
            try {
                /* @var Model $model */
                $model::query()->insert($models->toArray());
            } catch (Throwable $e) {
                if ($import instanceof SkipsOnError) {
                    $import->onError($e);
                } else {
                    throw $e;
                }
            }
        });
    }

    /**
     * @param  \Nikazooz\Simplesheet\Concerns\ToModel  $import
     * @return void
     */
    private function singleFlush(ToModel $import)
    {
        $this->rows()->each(function (array $attributes) use ($import) {
            $this->toModels($import, $attributes)->each(function (Model $model) use ($import) {
                try {
                    $model->saveOrFail();
                } catch (Throwable $e) {
                    if ($import instanceof SkipsOnError) {
                        $import->onError($e);
                    } else {
                        throw $e;
                    }
                }
            });
        });
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return \Illuminate\Database\Eloquent\Model
     */
    private function prepare(Model $model): Model
    {
        if ($model->usesTimestamps()) {
            $time = $model->freshTimestamp();

            $updatedAtColumn = $model->getUpdatedAtColumn();

            // If model has updated at column and not manually provided.
            if ($updatedAtColumn && null === $model->{$updatedAtColumn}) {
                $model->setUpdatedAt($time);
            }

            $createdAtColumn = $model->getCreatedAtColumn();

            // If model has created at column and not manually provided.
            if ($createdAtColumn && null === $model->{$createdAtColumn}) {
                $model->setCreatedAt($time);
            }
        }

        return $model;
    }

    /**
     * @param  \Nikazooz\Simplesheet\Concerns\WithValidation  $import
     * @return void
     *
     * @throws \Nikazooz\Simplesheet\Validators\ValidationException
     */
    private function validateRows(WithValidation $import)
    {
        try {
            $this->validator->validate($this->rows, $import);
        } catch (RowSkippedException $e) {
            foreach ($e->skippedRows() as $row) {
                unset($this->rows[$row]);
            }
        }
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    private function rows(): Collection
    {
        return new Collection($this->rows);
    }
}
