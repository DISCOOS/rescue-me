<?php

    /**
     * File containing: Inspector class
     * 
     * @copyright Copyright 2013 {@link http://www.discoos.org DISCOOS Foundation} 
     *
     * @since 03. July 2013, v. 8.00
     * 
     * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
     */

    /**
     * Inspector class
     * 
     * @package 
     */
    class Inspector
    {
        
        public static function namespaceOf($className, $separator='\\') {
            if (false !== ($lastNsPos = strripos($className, $separator))) {
                return substr($className, 0, $lastNsPos);
            } 
            return $separator;
        }
        
        public static function subclassesOf($className, $includePath=null, $separator='\\', $extension='.php') {

            $classes = array();
            
            $includePath = isset($includePath) ? $includePath : dirname(__FILE__); 

            $dir = new DirectoryIterator($includePath);
            
            $namespace = self::namespaceOf($className);
            
            foreach ($dir as $file) {
                if($file->isFile()) {
                    $basename = $file->getBasename($extension);
                    $subclassName = $namespace.$separator.$basename;
                    if(is_subclass_of($subclassName, $className)) {
                        $classes[$subclassName] = $basename;
                    }
                } elseif($file->isDir() && !$file->isDot()) {
                    $classes += self::subclassesOf($className, $file->getPathname(), $separator, $extension);
                }
            }
            return $classes;
        }
        
    }// Inspector
