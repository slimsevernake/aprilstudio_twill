<?php

namespace App\Http\Controllers\Admin;

use A17\Twill\Http\Controllers\Admin\ModuleController;
use Illuminate\Support\Facades\Config;

class TypeController extends ModuleController
{
    protected $moduleName = 'types';

    protected function getPermalinkBaseUrl() {
        return $this->request->getScheme() . '://' . Config::get('app.url') . '/'.$this->moduleName.'/';
    }
}
