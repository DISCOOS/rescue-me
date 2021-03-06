<?php

    /**
     * File containing: Inspector class
     * 
     * @copyright Copyright 2013 {@link http://www.discoos.org DISCO OS Foundation} 
     *
     * @since 03. July 2013
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
            $className = ltrim($className,$separator);
            if (false !== ($lastNsPos = strripos($className, $separator))) {
                return substr($className, 0, $lastNsPos);
            }
            return $separator;
        }

        public static function subclassesOf($className, $includePath = null, $abstract = false, $separator = '\\', $extension = '.php') {
            $classes = array();

            $includePath = isset($includePath) ? $includePath : dirname(__FILE__);
            if(!is_array($includePath)) {
                $includePath = array($includePath);
            }
            foreach($includePath as $path) {
                $dir = new DirectoryIterator($path);
                $className = ltrim($className,$separator);
                $namespace = self::namespaceOf($className);
                foreach ($dir as $file) {
                    /** @var SplFileInfo $file */
                    if($file->isFile()) {
                        $basename = $file->getBasename($extension);
                        $subclassName = ltrim($namespace.$separator.$basename,$separator);
                        if($subclassName !== 'Inspector' && class_exists($subclassName)) {
                            $class = new ReflectionClass($namespace.$separator.$basename);
                            if($class->isSubclassOf($className) && $class->isAbstract() === $abstract) {
                                $classes[$subclassName] = $basename;
                            }
                        }
                    } elseif($file->isDir() && !$file->isDot()) {
                        $classes += self::subclassesOf($className, $file->getPathname(), $abstract, $separator, $extension);
                    }
                }
            }
            return $classes;
        }

    }// Inspector