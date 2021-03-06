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

    use RescueMe\Finite\FiniteException;
    use RescueMe\Finite\Machine;
    use RescueMe\Finite\Trace\State\Delivered;
    use RescueMe\Finite\Trace\State\Located;
    use RescueMe\Finite\Trace\State\NotDelivered;
    use RescueMe\Finite\Trace\State\NotSent;
    use RescueMe\Finite\Trace\State\Responded;
    use RescueMe\Finite\Trace\State\Sent;
    use RescueMe\Finite\Trace\State\Alerted;
    use RescueMe\Finite\Trace\State\Timeout;
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
         * @throws FiniteException
         */
        public function build($sms) {

            $machine = new Machine();

            return $machine->addState(new Alerted())
                ->addState(new Sent())
                ->addState(new Delivered($sms))
                ->addState(new Responded())
                ->addState(new Located())
                ->addState(new NotSent())
                ->addState(new NotDelivered())
                ->addState(new Timeout())
                ->addTransition(Alerted::NAME, Sent::NAME)
                ->addTransition(Sent::NAME, Delivered::NAME)
                ->addTransition(Delivered::NAME, Responded::NAME)
                ->addTransition(Sent::NAME, Responded::NAME)
                ->addTransition(Responded::NAME, Located::NAME)
                ->addTransition(Alerted::NAME, NotSent::NAME)
                ->addTransition(Sent::NAME, NotDelivered::NAME)
                ->addTransition(Sent::NAME, Timeout::NAME)
                ->init();
        }

    }