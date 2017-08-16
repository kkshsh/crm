<?php
/**
 * 信息模版模型
 *
 * 2016-01-14
 * @author Vincent<nyewon@gmail.com>
 */
namespace App\Models\Message;

use App\Base\BaseModel;
use App\Models\Message\Template\TypeModel;

class TemplateModel extends BaseModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'message_templates';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type_id',
        'name',
        'content',
    ];

    public $searchFields = ['name'];

    public $appends = ['type_name'];

    /**
     * 更多搜索
     * @return array
     */
    public function getMixedSearchAttribute()
    {
        return [
            'relatedSearchFields' => [
            ],
            'filterFields' => [
            ],
            'filterSelects' => [

                'type_id' => TypeModel::where('parent_id','<>', 0)->get()->pluck('name', 'id'),

            ],
            'selectRelatedSearchs' => [
            ],
            //'sectionSelect' => ['time'=>['created_at']],
        ];
    }

    protected $rules = [
        'create' => [
            'type_id' => 'required',
            'name' => 'required|unique:message_templates,name',
            'content' => 'required',
        ],
        'update' => [
            'type_id' => 'required',
            'name' => 'required|unique:message_templates,name,{id}',
            'content' => 'required',
        ]
    ];

    public function type()
    {
        return $this->belongsTo('App\Models\Message\Template\TypeModel', 'type_id');
    }

    public function getTypeNameAttribute()
    {
        $type = $this->type;
        return ! empty($type) ? $type->name : '';
    }

    public function getTypeParentNameAttribute()
    {
        $type = $this->type;
        return ! empty($type) ? $type->parent->name : '';
    }

    public function getTypeParentIdAttribute()
    {
        $type = $this->type;
        return ! empty($type) ? $type->parent->id : 0;
    }

}
