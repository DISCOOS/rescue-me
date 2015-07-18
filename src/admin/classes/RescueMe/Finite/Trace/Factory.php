<?php
    /**
     * File containing: Trace state machine factory class
     *
     * @copyright Copyright 2015 {@link http://www.discoos.org DISCO OS Foundation}
     *
     * @since 28. March 2015
     *
     * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
     */

    namespace RescueMe\Finite\Trace;

    use RescueMe\Finite\Machine;
    use RescueMe\Finite\Trace\State\Closed;
    use RescueMe\Finite\Trace\State\Delivered;
    use RescueMe\Finite\Trace\State\DeliveryTimeout;
    use RescueMe\Finite\Trace\State\Located;
    use RescueMe\Finite\Trace\State\Accepted;
    use RescueMe\Finite\Trace\State\LocationTimeout;
    use RescueMe\Finite\Trace\State\Sent;
    use RescueMe\Finite\Trace\State\Created;
    use RescueMe\Finite\Trace\State\TraceTimeout;
    use RescueMe\SMS\Provider;


    /**
     * Class Trace state factory class
     *
     * @package RescueMe\Finite\Trace
     */
    class Factory {

        /**
         * Build class
         * @param Provider $sms
         * @param array $params
         * @return Machine
         */
        public function build($sms, $params) {

            $machine = new Machine();

            return $machine
                // Main states
                ->addState(new Created($sms))
                ->addState(new Sent())
                ->addState(new Delivered($sms))
                ->addState(new Accepted())
                // Success states
                ->addState(new Closed($params))
                ->addState(new Located($params))
                // Timeout states
                ->addState(new DeliveryTimeout($params))
                ->addState(new LocationTimeout($params))
                ->addState(new TraceTimeout($params))
                // Main transitions
                ->addTransition(Created::NAME, Sent::NAME)
                ->addTransition(Sent::NAME, Delivered::NAME)
                ->addTransition(Sent::NAME, Accepted::NAME)
                ->addTransition(Delivered::NAME, Accepted::NAME)
                // Recoverable timeout transitions
                ->addTransition(Sent::NAME, DeliveryTimeout::NAME)
                ->addTransition(DeliveryTimeout::NAME, Delivered::NAME)
                ->addTransition(Accepted::NAME, LocationTimeout::NAME)
                ->addTransition(LocationTimeout::NAME, Located::NAME)
                // Unrecoverable timeout transitions
                ->addTransition(Created::NAME, TraceTimeout::NAME)
                ->addTransition(Sent::NAME, TraceTimeout::NAME)
                ->addTransition(Delivered::NAME, TraceTimeout::NAME)
                ->addTransition(Accepted::NAME, TraceTimeout::NAME)
                // Success transitions
                ->addTransition(Accepted::NAME, Located::NAME)
                ->addTransition(Created::NAME, Closed::NAME)
                ->addTransition(Sent::NAME, Closed::NAME)
                ->addTransition(Delivered::NAME, Closed::NAME)
                ->addTransition(Accepted::NAME, Closed::NAME)
                ->addTransition(Located::NAME, Closed::NAME)
                ->init();
        }

    }