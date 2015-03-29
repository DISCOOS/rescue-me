<?php
    /**
     * File containing: State class
     *
     * @copyright Copyright 2015 {@link http://www.discoos.org DISCO OS Foundation}
     *
     * @since 08. March 2015
     *
     * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
     */

    namespace RescueMe\Finite;


    /**
     * Interface State
     * @package RescueMe\Finite
     */
    interface State {

        /**
         * Initial state (has only outbound transition)
         */
        const T_INITIAL = 'i';

        /**
         * Transit state (has inbound and outbound transitions)
         */
        const T_TRANSIT = 't';

        /**
         * Final state (has only inbound transition)
         */
        const T_FINAL = 'f';

        /**
         * Get state name
         * @return mixed
         */
        function getName();

        /**
         * Get state type
         * @return mixed
         */
        function getType();

        /**
         * Get state data
         * @return mixed
         */
        function getData();

        /**
         * Check if state accepts condition
         * @param mixed $condition
         * @return boolean
         */
        function accept($condition);

        /**
         * Check if state has been accepted
         * @return boolean
         */
        function isAccepted();

    }