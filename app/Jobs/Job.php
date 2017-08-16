<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\DispatchesJobs;
//use App\Models\Log\QueueModel as QueueLog;
//use App\Models\Event\CategoryModel;

abstract class Job
{
    /*
    |--------------------------------------------------------------------------
    | Queueable Jobs
    |--------------------------------------------------------------------------
    |
    | This job base class provides a central location to place any logic that
    | is shared across all of your jobs. The trait included with the class
    | provides access to the "onQueue" and "delay" queue helper methods.
    |
    */

    use Queueable, DispatchesJobs;

    protected $relation_id = 0;
    protected $description = 'init';
    protected $lasting = 0.00;
    protected $result = ['status' => 'init', 'remark' => 'init'];

    public function log($queue, $data = '')
    {
        QueueLog::create([
            'relation_id' => $this->relation_id,
            'queue' => $queue,
            'data' => $data,
            'description' => $this->description,
            'lasting' => $this->lasting,
            'result' => $this->result['status'],
            'remark' => $this->result['remark']
        ]);
    }

    public function eventLog($user, $content = '', $to = '', $from = '')
    {
        $modelName = $this->table;
        if ($modelName) {
            $category = CategoryModel::where('model_name', $modelName)->first();
            if (!$category) {
                $category = CategoryModel::create(['model_name' => $modelName]);
            }
            $category->child()->create([
                'type_id' => ($to ? json_decode($to)->id : ''),
                'what' => $content,
                'when' => date('Y-m-d H:i:s', time()),
                'to_arr' => $to,
                'from_arr' => $from,
                'who' => $user
            ]);
        }
    }
}
