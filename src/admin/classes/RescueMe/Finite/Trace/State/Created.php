<?php
/**
 * File containing: Trace state alerted class
 *
 * @copyright Copyright 2015 {@link http://www.discoos.org DISCO OS Foundation}
 *
 * @since 28. March 2015
 *
 * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
 */

namespace RescueMe\Finite\Trace\State;

use RescueMe\Finite\AbstractState;
use RescueMe\Finite\State;
use RescueMe\Domain\Missing;
use RescueMe\SMS\Provider;


/**
 * Trace state alerted
 * @package RescueMe\Finite\Trace\State
 */
class Created extends AbstractState {

    const NAME = 'Created';

    /**
     * SMS provider
     * @var Provider
     */
    private $sms;

    /**
     * Constructor
     * @param Provider $sms
     */
    function __construct($sms) {
        parent::__construct(self::NAME, State::T_INITIAL);
        $this->sms = $sms;
    }

    /**
     * Check if trace state is Created (reported date exists)
     * @param Missing $condition
     * @return mixed
     */
    protected function onAccept($condition) {
        $this->data = $condition->reported;
        return is_null($this->data) === false;
    }

    /**
     * Get SMS provider
     *
     * @return \RescueMe\SMS\Provider
     */
    public function getProvider()
    {
        return $this->sms;
    }


    /**
     * Check if SMS provider is installed
    * @return bool
     */
    public function isProviderNotInstalled() {
        return $this->sms === FALSE;
    }

    /**
     * Check if SMS provider is configured correctly
    * @return bool
     */
    public function isProviderConfigInvalid() {
        return $this->sms->validate() === FALSE;
    }

}