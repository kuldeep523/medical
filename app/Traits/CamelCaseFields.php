<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait CamelCaseFields
{
    protected static function bootCamelCaseFields()
    {
        static::saving(function ($model) {
            $fields = $model->getCamelCaseAttributes();
            foreach ($fields as $field) {
                if (isset($model->attributes[$field]) && is_string($model->attributes[$field])) {
                    $model->attributes[$field] = Str::title($model->attributes[$field]);
                }
            }
        });
    }

    public function getCamelCaseAttributes(): array
    {
        return $this->camelCaseAttributes ?? [];
    }
}
