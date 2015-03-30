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
    use RescueMe\Finite\Trace\State\Delivered;
    use RescueMe\Finite\Trace\State\Located;
    use RescueMe\Finite\Trace\State\Accepted;
    use RescueMe\Finite\Trace\State\Sent;
    use RescueMe\Finite\Trace\State\Created;
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
         * @return Machine
         */
        public function build($sms) {

            $machine = new Machine();

            return $machine->addState(new Created())
                ->addState(new Sent())
                ->addState(new Delivered($sms))
                ->addState(new Accepted())
                ->addState(new Located())
                ->addTransition(Created::NAME, Sent::NAME)
                ->addTransition(Sent::NAME, Delivered::NAME)
                ->addTransition(Delivered::NAME, Accepted::NAME)
                ->addTransition(Sent::NAME, Accepted::NAME)
                ->addTransition(Accepted::NAME, Located::NAME)
                ->init();
        }

    }