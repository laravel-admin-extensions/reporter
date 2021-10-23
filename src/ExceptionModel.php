<?php

namespace Encore\Admin\Reporter;

use Illuminate\Database\Eloquent\Model;

class ExceptionModel extends Model
{
    public static $methodColor = [
        'GET'       => 'green',
        'POST'      => 'yellow',
        'PUT'       => 'blue',
        'DELETE'    => 'red',
        'PATCH'     => 'black',
        'OPTIONS'   => 'grey',
    ];

    protected $fillable = [
        'type',
        'code',
        'message',
        'file',
        'line',
        'trace',
        'method',
        'path',
        'query',
        'body',
        'cookies',
        'headers',
        'ip',
    ];

    /**
     * Settings constructor.
     *
     * @param array $attributes
     */
    public function __construct($attributes = [])
    {
        parent::__construct($attributes);

        $this->setConnection(config('admin.database.connection') ?: config('database.default'));

        $this->setTable(config('admin.extensions.reporter.table', 'laravel_exceptions'));
    }
}
