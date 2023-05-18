<?php

use Mvseed\Application\Template;

if (! function_exists('dd')) {
    function dd($variable) {
        echo '<pre/>';
        print_r($variable);
        exit;
    }
}

if (! function_exists('view')) {
    function view($view_name, $view_vars=[], $layout_name=null, $layout_vars=[]) {
        $engine = new Template();
        $engine->render($view_name, $view_vars, $layout_name, $layout_vars);
    }
}
