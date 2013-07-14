<?php

    /**
     * Get Minify groups configuration array
     * 
     */
    function get_rescueme_minify_config()
    {
        // NOTE: add dependencies before dependents!
        return array
        (
            'index.less' => array
            (
                '//css/bootstrap.min.css', 
                '//admin/css/admin.css'
            ),
            'index.js' => array
            (
                '//js/rescueme.js', 
                '//js/jquery-1.9.1.min.js', 
                '//js/less.min.js',
                '//js/bootstrap.min.js'
            ),
            'admin.less' => array
            (
                '//css/bootstrap.min.css', 
                '//admin/css/admin.css'
            ),
            'admin.js' => array
            (
                '//js/rescueme.js', 
                '//js/jquery-1.9.1.min.js', 
                '//js/less.min.js', 
                '//js/capslock.js',
                '//js/bootstrap.min.js',
                '//admin/js/admin.js'
            ),
            'track.js' => array
            (
                '//js/rescueme.js', 
                '//track/js/track.js'
            ),
            
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
    function rescueme_js($content, $type) {

        // Is content type JS?
        if ($type === Minify::TYPE_JS) {

                // Load RescueMe configuration
                require '../config.php';

                // Get concatenated js
                $content = get_rescueme_js($content);

        }
        return $content;
    }
    
    $min_serveOptions['postprocessor'] = 'rescueme_js';    
    