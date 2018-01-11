<?php

namespace WpPluginner\Framework\Concern;

use Illuminate\Support\HtmlString;
use Symfony\Component\Debug\Exception\FatalThrowableError;

trait ContainerTrait
{
    public function request()
    {
        if ($this->bound('request')) {
            return $this['request'];
        } else {
            throw new \Exception('Illuminate Request Disabled');
        }
    }

    public function response()
    {
        if ($this->bound('response')) {
            return $this['response'];
        } else {
            throw new \Exception('Illuminate Response Disabled');
        }
    }

    public function session()
    {
        if ($this->bound('session')) {
            return $this['session'];
        } else {
            throw new \Exception('Illuminate Session Disabled');
        }
    }

    public function config()
    {
        if ($this->bound('config')) {
            return $this['config'];
        } else {
            throw new \Exception('Illuminate Config Disabled');
        }
    }

    public function storage()
    {
        if ($this->bound('filesystem')) {
            return $this['filesystem'];
        } else {
            throw new \Exception('Illuminate Filesystem Disabled');
        }
    }

    public function cache()
    {
        if ($this->bound('cache')) {
            return $this['cache'];
        } else {
            throw new \Exception('Illuminate Cache Disabled');
        }

    }

    public function db()
    {
        if ($this->bound('db')) {
            return $this['db'];
        } else {
            throw new \Exception('Illuminate Database Disabled');
        }
    }

    public function schema()
    {
        if ($this->bound('db')) {
            return $this['db']->connection()->getSchemaBuilder();
        } else {
            throw new \Exception('Illuminate Database Disabled');
        }
    }

    public function option()
    {
        return $this['plugin.option'];
    }

    public function view($view = null, $data = [], $mergeData = [])
    {
        $viewFactory = $this['view'];
        if (func_num_args() === 0) {
            return $viewFactory;
        }
        return $viewFactory->make($view, $data, $mergeData);
    }

    public function admin()
    {
        return $this['plugin.admin'];
    }

    public function ajax()
    {
        return $this['plugin.ajax'];
    }
}
