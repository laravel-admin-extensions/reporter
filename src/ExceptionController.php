<?php

namespace Encore\Admin\Reporter;

use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Reporter\Tracer\Parser;

class ExceptionController
{
    use ModelForm;

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {
            $content->header('Exception');
            $content->description('Exception list..');

            $content->body($this->grid());
        });
    }

    public function grid()
    {
        return Admin::grid(ExceptionModel::class, function (Grid $grid) {
            $grid->model()->orderBy('id', 'desc');

            $grid->id('ID')->sortable();

            $grid->type()->display(function ($type) {
                $path = explode('\\', $type);

                return array_pop($path);
            });

            $grid->code();
            $grid->message()->style('width:400px')->display(function ($message) {
                if (empty($message)) {
                    return '';
                }

                return "<code>$message</code>";
            });

            $grid->request()->display(function () {
                $color = ExceptionModel::$methodColor[$this->method];

                return sprintf(
                    '<span class="label bg-%s">%s</span><code>%s</code>',
                    $color,
                    $this->method,
                    $this->path
                );
            });

            $grid->input()->display(function ($input) {
                $input = json_decode($input, true);

                if (empty($input)) {
                    return '';
                }

                return '<pre>'.json_encode($input, JSON_PRETTY_PRINT).'</pre>';
            });

            $grid->created_at();

            $grid->filter(function ($filter) {
                $filter->disableIdFilter();
                $filter->like('type');
                $filter->like('message');
            });

            $grid->disableCreation();

            $grid->actions(function (Grid\Displayers\Actions $actions) {
                $actions->disableEdit();

                $path = $actions->getResource().'/'.$actions->getKey();
                $actions->prepend("<a href=\"$path\"><i class=\"fa fa-search-minus\"></i></a>");
            });
        });
    }

    public function show($id)
    {
        return Admin::content(function (Content $content) use ($id) {
            $content->header('Exception');
            $content->description('Exception detail.');

            Admin::script('Prism.highlightAll();');

            $exception = ExceptionModel::findOrFail($id);
            $trace = "#0 {$exception->file}({$exception->line})\n";
            $frames = (new Parser($trace.$exception->trace))->parse();
            $cookies = json_decode($exception->cookies, true);
            $headers = json_decode($exception->headers, true);

            array_pop($frames);

            $view = view('laravel-admin-reporter::exception', compact('exception', 'frames', 'cookies', 'headers'));

            $content->body($view);
        });
    }

    public function destroy($id)
    {
        $ids = explode(',', $id);

        if (ExceptionModel::whereIn('id', $ids)->delete()) {
            return response()->json([
                'status'  => true,
                'message' => trans('admin.delete_succeeded'),
            ]);
        } else {
            return response()->json([
                'status'  => false,
                'message' => trans('admin.delete_failed'),
            ]);
        }
    }
}
