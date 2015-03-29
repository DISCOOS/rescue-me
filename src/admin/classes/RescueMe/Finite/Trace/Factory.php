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
    use RescueMe\Finite\Trace\State\Answered;
    use RescueMe\Finite\Trace\State\Sent;
    use RescueMe\Finite\Trace\State\Alerted;
    use RescueMe\SMS\Check;
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

            return $machine->addState(new Alerted())
                ->addState(new Sent())
                ->addState(new Delivered($sms))
                ->addState(new Answered())
                ->addState(new Located())
                ->addTransition(Alerted::NAME, Sent::NAME)
                ->addTransition(Sent::NAME, Delivered::NAME)
                ->addTransition(Delivered::NAME, Answered::NAME)
                ->addTransition(Sent::NAME, Answered::NAME)
                ->addTransition(Answered::NAME, Located::NAME)
                ->init();
        }

    }