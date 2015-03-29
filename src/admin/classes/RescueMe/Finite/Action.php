<?php
    /**
     * File containing: Action class
     *
     * @copyright Copyright 2015 {@link http://www.discoos.org DISCO OS Foundation}
     *
     * @since 28. March 2015
     *
     * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
     */

    namespace RescueMe\Finite;


    /**
     * Interface Action
     * @package RescueMe\Finite
     */
    interface Action {

        /**
         * Execute action
         * @return mixed
         */
        function execute();

    }