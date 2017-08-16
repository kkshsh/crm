<?php
/**
 * 包裹产品模型
 *
 * 2016-01-13
 * @author Vincent<nyewon@gmail.com>
 */
namespace App\Models\Order\Package;

use Illuminate\Database\Eloquent\Model;

class ItemModel extends Model
{
    protected $connection = 'workstation';
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'packageitem';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];

    public function package()
    {
        return $this->belongsTo('App\Models\PackageModel', 'package_id');
    }

    public function item()
    {
        return $this->belongsTo('App\Models\ItemModel', 'item_id');
    }

}
