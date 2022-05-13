<?php

namespace Lysice\Visits\Models;

use Illuminate\Database\Eloquent\Model;

class TopicViews extends Model
{
    protected $tableName = '';
    public function __construct(array $attributes = [])
    {
        $this->tableName = config('laravel-visit.table');
        parent::__construct($attributes);
    }

    protected $keyType = 'string';
    protected $primaryKey = 'uuid';

    /**
     * @param string $table
     */
    public function setTable($table): void
    {
        $this->table = $this->tableName;
    }

    protected $fillable = [
        'uuid', 'view_count', 'type'
    ];

    /**
     * @param $type
     * @param $uuid
     * @param $viewCount
     * @return mixed
     */
    public static function sync($type, $uuid, $viewCount)
    {
        return TopicViews::updateOrCreate([
            'type' => $type,
            'uuid' => $uuid
        ], [
            'view_count' => $viewCount,
            'type' => $type
        ]);
    }

    /**
     * @param $uuid
     * @return |null
     */
    public static function getViewCountById($uuid)
    {
        $model = TopicViews::where('uuid', $uuid)->first();
        return empty($model) ? null : $model->view_count;
    }
}
