<?php

if (!function_exists('load_assets')) {
    function load_assets() {
        $html = '';
        
        // CSS
        foreach (config('assets.css') as $css) {
            $html .= '<link rel="stylesheet" href="' . $css . '">' . PHP_EOL;
        }
        
        // JS
        foreach (config('assets.js') as $js) {
            $html .= '<script src="' . $js . '"></script>' . PHP_EOL;
        }
        
        return $html;
    }
}