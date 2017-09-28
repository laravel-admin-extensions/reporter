<?php

namespace Encore\Admin\Reporter;

use Encore\Admin\Admin;

trait BootExtension
{
    public static function boot()
    {
        static::registerRoutes();

        static::importAssets();

        Admin::extend('reporter', __CLASS__);
    }

    /**
     * Register routes for laravel-admin.
     *
     * @return void
     */
    protected static function registerRoutes()
    {
        parent::routes(function ($router) {
            /* @var \Illuminate\Routing\Router $router */
            $router->resource('exceptions', 'Encore\Admin\Reporter\ExceptionController');
        });
    }

    /**
     * {@inheritdoc}
     */
    public static function import()
    {
        parent::createMenu('Exception Reporter', 'exceptions', 'fa-bug');

        parent::createPermission('Exceptions reporter', 'ext.reporter', 'exceptions*');
    }

    public static function importAssets()
    {
        Admin::js('/vendor/laravel-admin-reporter/prism/prism.js');
        Admin::css('/vendor/laravel-admin-reporter/prism/prism.css');
    }
}
