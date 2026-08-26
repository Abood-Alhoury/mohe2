<?php

namespace App\Models\Builders;

use Illuminate\Database\Eloquent\Builder;
use App\Models\ApplicationStatus;
use App\Models\ApplicationRequestType;

class ApplicationBuilder extends Builder
{
    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        if (($column === 'status' || $column === 'applications.status') && is_string($value) && !is_numeric($value)) {
            $statusId = ApplicationStatus::where('name', $value)->value('id');
            if ($statusId) {
                return parent::where($column, $operator, $statusId, $boolean);
            }
        }

        if (($column === 'request_type' || $column === 'applications.request_type') && is_string($value) && !is_numeric($value)) {
            $typeId = ApplicationRequestType::where('name', $value)->value('id');
            if ($typeId) {
                return parent::where($column, $operator, $typeId, $boolean);
            }
        }

        return parent::where($column, $operator, $value, $boolean);
    }

    public function whereIn($column, $values, $boolean = 'and', $not = false)
    {
        if ($column === 'status' || $column === 'applications.status') {
            $transformed = [];
            foreach ($values as $val) {
                if (is_string($val) && !is_numeric($val)) {
                    $id = ApplicationStatus::where('name', $val)->value('id');
                    if ($id) {
                        $transformed[] = $id;
                    }
                } else {
                    $transformed[] = $val;
                }
            }
            return parent::whereIn($column, $transformed, $boolean, $not);
        }

        if ($column === 'request_type' || $column === 'applications.request_type') {
            $transformed = [];
            foreach ($values as $val) {
                if (is_string($val) && !is_numeric($val)) {
                    $id = ApplicationRequestType::where('name', $val)->value('id');
                    if ($id) {
                        $transformed[] = $id;
                    }
                } else {
                    $transformed[] = $val;
                }
            }
            return parent::whereIn($column, $transformed, $boolean, $not);
        }

        return parent::whereIn($column, $values, $boolean, $not);
    }
}
