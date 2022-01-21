<?php

namespace Encore\Admin\Reporter;

use Encore\Admin\Extension;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Throwable;

class Reporter extends Extension
{
    use BootExtension;

    /**
     * @var Request
     */
    protected $request;

    /**
     * Reporter constructor.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @param Throwable $exception
     *
     * @return void
     */
    public static function report(Throwable $exception)
    {
        $reporter = new static(request());

        $reporter->reportException($exception);
    }

    /**
     * @param Throwable $exception
     *
     * @return void
     */
    public function reportException(Throwable $exception)
    {
        $data = [

            // Request info.
            'method'    => $this->request->getMethod(),
            'ip'        => $this->request->getClientIps(),
            'path'      => $this->request->path(),
            'query'     => Arr::except($this->request->all(), ['_pjax', '_token', '_method', '_previous_']),
            'body'      => $this->request->getContent(),
            'cookies'   => $this->request->cookies->all(),
            'headers'   => Arr::except($this->request->headers->all(), 'cookie'),

            // Exception info.
            'type'      => get_class($exception),
            'code'      => $exception->getCode(),
            'file'      => $exception->getFile(),
            'line'      => $exception->getLine(),
            'message'   => $exception->getMessage(),
            'trace'     => $exception->getTraceAsString(),
        ];

        $data = $this->stringify($data);

        try {
            $this->store($data);
        } catch (Throwable $e) {
//            $result = $this->reportException($e);
        }

//        return $result;
    }

    /**
     * Convert all items to string.
     *
     * @param $data
     *
     * @return array
     */
    public function stringify($data): array
    {
        return array_map(function ($item) {
            return is_array($item) ? json_encode($item, JSON_OBJECT_AS_ARRAY) : (string) $item;
        }, $data);
    }

    /**
     * Store exception info to db.
     *
     * @param array $data
     *
     * @return bool
     */
    public function store(array $data): bool
    {
        try {
            ExceptionModel::query()->create($data);

            return true;
        } catch (Throwable $e) {
            return false;
            //dd($e);
        }
    }
}
