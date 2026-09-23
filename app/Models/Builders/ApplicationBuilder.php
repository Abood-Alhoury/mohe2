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

        $isStatusCol = in_array($column, ['status', 'applications.status']);
        $isTypeCol = in_array($column, ['request_type', 'applications.request_type']);

        if ($isStatusCol && is_string($value) && !is_numeric($value)) {
            $lowerOp = strtolower(trim((string)$operator));
            if ($value === 'إنتاج علمي' || $value === 'بانتظار لجنة الإنتاج العلمي') {
                $statusId = ApplicationStatus::where('name', 'بانتظار لجنة إنتاج علمي')->value('id') ?? 5;
                return parent::where($column, $operator, $statusId, $boolean);
            }
            if ($lowerOp === 'like') {
                $ids = ApplicationStatus::where('name', 'like', $value)->pluck('id')->toArray();
                return parent::whereIn($column, $ids, $boolean);
            } elseif ($lowerOp === 'not like') {
                $ids = ApplicationStatus::where('name', 'like', $value)->pluck('id')->toArray();
                return parent::whereNotIn($column, $ids, $boolean);
            } else {
                $statusId = ApplicationStatus::where('name', $value)->value('id');
                if ($statusId) {
                    return parent::where($column, $operator, $statusId, $boolean);
                }
            }
        }

        if ($isTypeCol && is_string($value) && !is_numeric($value)) {
            $lowerOp = strtolower(trim((string)$operator));
            if ($value === 'دكتوراه خارجية') {
                $typeId = ApplicationRequestType::where('name', 'دكتورة خارجية')->value('id') ?? 7;
                return parent::where($column, $operator, $typeId, $boolean);
            }
            if ($lowerOp === 'like') {
                $ids = ApplicationRequestType::where('name', 'like', $value)->pluck('id')->toArray();
                return parent::whereIn($column, $ids, $boolean);
            } elseif ($lowerOp === 'not like') {
                $ids = ApplicationRequestType::where('name', 'like', $value)->pluck('id')->toArray();
                return parent::whereNotIn($column, $ids, $boolean);
            } else {
                $typeId = ApplicationRequestType::where('name', $value)->value('id');
                if ($typeId) {
                    return parent::where($column, $operator, $typeId, $boolean);
                }
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
                    if ($val === 'إنتاج علمي' || $val === 'بانتظار لجنة الإنتاج العلمي') {
                        $id = ApplicationStatus::where('name', 'بانتظار لجنة إنتاج علمي')->value('id') ?? 5;
                    } else {
                        $id = ApplicationStatus::where('name', $val)->value('id');
                    }
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
                    if ($val === 'دكتوراه خارجية') {
                        $id = ApplicationRequestType::where('name', 'دكتورة خارجية')->value('id') ?? 7;
                    } else {
                        $id = ApplicationRequestType::where('name', $val)->value('id');
                    }
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

    public function whereNotIn($column, $values, $boolean = 'and')
    {
        return $this->whereIn($column, $values, $boolean, true);
    }
}
