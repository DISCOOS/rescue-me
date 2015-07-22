<?php
/**
 * File containing: AbstractProvider class
 *
 * @copyright Copyright 2015 {@link http://www.discoos.org DISCO OS Foundation}
 *
 * @since 22. July 2015
 *
 * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
 */

namespace RescueMe\Email;


use RescueMe\AbstractModule;
use RescueMe\Configuration;
use RescueMe\User;

/**
 * Class AbstractProvider
 * @package RescueMe\Email
 */
abstract class AbstractProvider extends AbstractModule implements Provider {

    /**
     * Constructor
     *
     * @param $config Configuration Configuration
     *
     * @since 22. March 2015
     *
     */
    public function __construct($config)
    {
        parent::__construct($config);
    }

    /**
     * Set the email address from which you want the message to be sent
     * @param string|User|array $from
     * @return AbstractProvider
     */
    public function setFrom($from) {
        $this->config->set('from', $from);
        return $this;
    }

    /**
     * Set the recipient's email address and name
     * @param string|User|array $to
     * @return Provider
     */
    public function setTo($to) {
        $this->config->set('to', $to);
        return $this;
    }

    /**
     * Set the bulk send mode
     * @param boolean $bulk
     * @return Provider
     */
    public function setBulk($bulk) {
        $this->config->set('bulk', $bulk);
        return $this;
    }

    /**
     * Set email subject
     * @param string $subject
     * @return Provider
     */
    public function setSubject($subject) {
        $this->config->set('subject', $subject);
        return $this;
    }

    /**
     * Set email body
     * @param string $body
     * @return Provider
     */
    public function setBody($body) {
        $this->config->set('body', $body);
        return $this;
    }


    /**
     * Prepare addresses
     * @param string|User|array $data
     * @return array
     */
    protected function prepareAddresses($data) {
        $addresses = array();
        if($data instanceof User) {
            $addresses[] = array($data->email => $data->name);
        }
        elseif(is_string($data)) {
            $addresses[] = $data;
        }
        elseif(is_array($data)) {
            foreach($data as $address) {
                $addresses += $this->prepareAddresses($address);
            }
        }
        return $addresses;
    }




} 