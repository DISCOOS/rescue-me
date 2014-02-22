<?php

    // Minify constants
    define('MINIFY_MAXAGE', 1800);
    
    /**
     * Get Minify groups configuration array
     * 
     */
    function get_rescueme_minify_config()
    {
        // NOTE: add dependencies before dependents!
        return array
        (
            'index.css' => array
            (
                '//css/bootstrap.min.css', 
                '//css/bootstrap.fix.css', 
                '//admin/css/admin.css'
            ),
            'index.js' => array
            (
                '//js/rescueme.js', 
                '//js/jquery.min.js', 
                '//js/bootstrap.min.js'
            ),
            'admin.css' => array
            (
                '//css/bootstrap.min.css', 
                '//css/bootstrap.fix.css', 
                '//css/bootstrap-editable.css', 
                '//admin/css/admin.css',
                '//admin/css/admin.responsive.css',
                '//admin/css/map.css'

            ),
            'admin.js' => array
            (
                '//js/rescueme.js', 
                '//js/jquery.min.js', 
                '//js/jquery.ui-custom.min.js', 
                '//js/jquery.validate.min.js', 
                '//js/capslock.js',
                '//js/bootstrap.min.js',
                '//js/bootstrap-editable.min.js', 
                '//js/validate.js',
                '//admin/js/admin.js'
            ),
            'map.js' => array
            (
                '//admin/js/map.js'
            )
            
        );        
    }// get_rescueme_minify_config

    
    /** 
     * Processing Output After Minification
     * 
     * If $min_serveOptions['postprocessor'] is set to a callback, 
     * Minify will pass the minified content to this function with type 
     * as the second argument. This allows you to apply changes to your 
     * minified content without making your own custom minifier. 
     * 
     * Rescue Me! uses this to wrap minified js source files with 
     * a self-invoking function which pass configurable Rescue Me! 
     * properties to minified Rescue Me! js source code.
     * 
     * For more information, see 
     * 
     * http://sarfraznawaz.wordpress.com/2012/01/26/javascript-self-invoking-functions/
     */
    function rescueme_minify_prepare($content, $type) {

        // Is content type JS?
        if ($type === Minify::TYPE_JS) {

            require '../config.php';

            $install = get_rescueme_install();
            
            // Get js wrapped inside self-invoking function.
            $content = "(function(window,document,install,undefined){".$content."}(window,document,$install));";

        }
        else if ($type === Minify::TYPE_CSS) {

            // Load RescueMe configuration
            require '../config.php';
            
            // Replace all data urls
            $content = preg_replace('#url\("/#', 'url("'.APP_URI, $content);

        }
        return $content;
    }
    
    $min_serveOptions['postprocessor'] = 'rescueme_minify_prepare';    
    