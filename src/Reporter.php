<?php

namespace Encore\Admin\Reporter;

use Encore\Admin\Auth\Database\Menu;
use Encore\Admin\Auth\Database\Permission;
use Encore\Admin\Extension;
use Encore\Admin\Facades\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class Reporter extends Extension
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * Reporter constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public static function boot()
    {
        static::registerRoutes();

        static::importAssets();
    }

    /**
     * Register routes for laravel-admin.
     *
     * @return void
     */
    public static function registerRoutes()
    {
        /* @var \Illuminate\Routing\Router $router */
        Route::group(['prefix' => config('admin.route.prefix')], function ($router) {

            $attributes = array_merge([
                'middleware' => config('admin.route.middleware'),
            ], static::config('route', []));

            Route::group($attributes, function ($router) {

                /* @var \Illuminate\Routing\Router $router */
                $router->resource('exceptions', 'Encore\Admin\Reporter\ExceptionController');
            });

        });
    }

    public static function importAssets()
    {
        Admin::js('/vendor/laravel-admin-reporter/prism/prism.js');
        Admin::css('/vendor/laravel-admin-reporter/prism/prism.css');
    }

    /**
     * @param \Exception $exception
     * @return mixed
     */
    public static function report(\Exception $exception)
    {
        $reporter = new static(request());

        return $reporter->reportException($exception);
    }

    /**
     * @param \Exception $exception
     * @return bool
     */
    public function reportException(\Exception $exception)
    {
        $data = [

            // Request info.
            'method'    => $this->request->getMethod(),
            'ip'        => $this->request->getClientIps(),
            'path'      => $this->request->path(),
            'input'     => array_except($this->request->all(), ['_pjax', '_token', '_method', '_previous_']),
            'body'      => $this->request->getContent(),
            'cookies'   => $this->request->cookies->all(),
            'headers'   => array_except($this->request->headers->all(), 'cookie'),

            // Exception info.
            'exception' => get_class($exception),
            'code'      => $exception->getCode(),
            'file'      => $exception->getFile(),
            'line'      => $exception->getLine(),
            'message'   => $exception->getMessage(),
            'trace'     => $exception->getTraceAsString(),
        ];

        $data = $this->stringify($data);

        try {
            $result = $this->store($data);
        } catch (\Exception $e) {
            $result = $this->reportException($e);
        }

        return $result;
    }

    /**
     * Convert all items to string.
     *
     * @param $data
     * @return array
     */
    public function stringify($data)
    {
        return array_map(function ($item) {
            return is_array($item) ? json_encode($item, JSON_OBJECT_AS_ARRAY) : (string)$item;
        }, $data);
    }

    /**
     * Store exception info to db.
     *
     * @param array $data
     * @return bool
     */
    public function store(array $data)
    {
        $exception = new Exception();

        $exception->type    = $data['exception'];
        $exception->code    = $data['code'];
        $exception->message = $data['message'];
        $exception->file    = $data['file'];
        $exception->line    = $data['line'];
        $exception->trace   = $data['trace'];
        $exception->method  = $data['method'];
        $exception->path    = $data['path'];
        $exception->input   = $data['input'];
        $exception->body    = $data['body'];
        $exception->cookies = $data['cookies'];
        $exception->headers = $data['headers'];
        $exception->ip      = $data['ip'];

        try {
            $exception->save();
        } catch (\Exception $e) {
            dd($e);
        }

        return $exception->save();
    }

    public static function import()
    {
        $lastOrder = Menu::max('order');

        // Add a menu.
        Menu::create([
            'parent_id' => 0,
            'order'     => $lastOrder + 1,
            'title'     => 'Exception Reporter',
            'icon'      => 'fa-bug',
            'uri'       => 'exceptions',
        ]);

        // Add a permission.
        Permission::create([
            'name'          => 'Exceptions reporter',
            'slug'          => 'ext.reporter',
            'http_path'     => admin_base_path('exceptions*'),
        ]);
    }
}