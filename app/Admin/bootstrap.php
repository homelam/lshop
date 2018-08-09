<?php

use App\Admin\Extensions\Form\BaiduEditor; 
use Encore\Admin\Form;

Form::forget(['map']);
Form::extend('ueditor', BaiduEditor::class);
