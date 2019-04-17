<?php

namespace Nikazooz\Simplesheet\Validators;

use Illuminate\Contracts\Support\Arrayable;

class Failure implements Arrayable
{
    /**
     * @var int
     */
    protected $row;

    /**
     * @var string
     */
    protected $attribute;

    /**
     * @var array
     */
    protected $errors;

    /**
     * @param  int  $rowNumber
     * @param  string  $attributeName
     * @param  array  $errors
     */
    public function __construct(int $rowNumber, string $attributeName, array $errors)
    {
        $this->row = $rowNumber;
        $this->attribute = $attributeName;
        $this->errors = $errors;
    }

    /**
     * @return int
     */
    public function row(): int
    {
        return $this->row;
    }

    /**
     * @return string
     */
    public function attribute(): string
    {
        return $this->attribute;
    }

    /**
     * @return array
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return collect($this->errors)->map(function ($message) {
            return __('There was an error on row :row. :message', ['row' => $this->row, 'message' => $message]);
        })->all();
    }
}
