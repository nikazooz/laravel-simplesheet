<?php

namespace Nikazooz\Simplesheet\Validators;

use Illuminate\Contracts\Validation\Factory;
use Nikazooz\Simplesheet\Concerns\SkipsOnFailure;
use Nikazooz\Simplesheet\Concerns\WithValidation;
use Nikazooz\Simplesheet\Exceptions\RowSkippedException;
use Illuminate\Validation\ValidationException as IlluminateValidationException;

class RowValidator
{
    /**
     * @var \Illuminate\Contracts\Validation\Factory
     */
    private $validator;

    /**
     * @param  \Illuminate\Contracts\Validation\Factory  $validator
     */
    public function __construct(Factory $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param  array  $rows
     * @param  \Nikazooz\Simplesheet\Concerns\WithValidation  $import
     * @return void
     *
     * @throws \Nikazooz\Simplesheet\Validators\ValidationException
     */
    public function validate(array $rows, WithValidation $import)
    {
        $rules = $this->rules($import);
        $messages = $this->messages($import);
        $attributes = $this->attributes($import);

        try {
            $this->validator->make($rows, $rules, $messages, $attributes)->validate();
        } catch (IlluminateValidationException $e) {
            $failures = [];
            foreach ($e->errors() as $attribute => $messages) {
                $row = strtok($attribute, '.');
                $attributeName = strtok('');
                $attributeName = $attributes['*.' . $attributeName] ?? $attributeName;

                $failures[] = new Failure(
                    $row,
                    $attributeName,
                    str_replace($attribute, $attributeName, $messages)
                );
            }

            if ($import instanceof SkipsOnFailure) {
                $import->onFailure(...$failures);
                throw new RowSkippedException(...$failures);
            } else {
                throw new ValidationException(
                    $e,
                    $failures
                );
            }
        }
    }

    /**
     * @param  \Nikazooz\Simplesheet\Concerns\WithValidation  $import
     * @return array
     */
    private function messages(WithValidation $import): array
    {
        return method_exists($import, 'customValidationMessages')
            ? $this->formatKey($import->customValidationMessages())
            : [];
    }

    /**
     * @param  \Nikazooz\Simplesheet\Concerns\WithValidation  $import
     * @return array
     */
    private function attributes(WithValidation $import): array
    {
        return method_exists($import, 'customValidationAttributes')
            ? $this->formatKey($import->customValidationAttributes())
            : [];
    }

    /**
     * @param  \Nikazooz\Simplesheet\Concerns\WithValidation  $import
     * @return array
     */
    private function rules(WithValidation $import): array
    {
        return $this->formatKey($import->rules());
    }

    /**
     * @param  array  $elements
     * @return array
     */
    private function formatKey(array $elements): array
    {
        return collect($elements)->mapWithKeys(function ($rule, $attribute) {
            $attribute = starts_with($attribute, '*.') ? $attribute : '*.' . $attribute;

            return [$attribute => $rule];
        })->all();
    }
}
