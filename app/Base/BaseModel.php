<?php

namespace App\Base;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BaseModel extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $rules;

    public function rules($type, $id = '')
    {
        $rules = $this->rules[$type];
        if ($id) {
            foreach ($rules as $column => $rule) {
                $rules[$column] = str_replace('{id}', $id, $rule);
            }
        }
        return $rules;
    }

}
