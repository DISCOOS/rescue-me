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
use RescueMe\Domain\User;

/**
 * Class AbstractProvider
 * @package RescueMe\Email
 */
abstract class AbstractProvider extends AbstractModule implements Provider {

    /**
     * Message data
     * @var array
     */
    protected $data = array();

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
        $this->data['from'] = $from;
        return $this;
    }

    /**
     * Set the recipient's email address and name
     * @param string|User|array $to
     * @return Provider
     */
    public function setTo($to) {
        $this->data['to'] = $to;
        return $this;
    }

    /**
     * Set the bulk send mode
     * @param boolean $bulk
     * @return Provider
     */
    public function setBulk($bulk) {
        $this->data['bulk'] = $bulk;
        return $this;
    }

    /**
     * Set email subject
     * @param string $subject
     * @return Provider
     */
    public function setSubject($subject) {
        $this->data['subject'] = $subject;
        return $this;
    }

    /**
     * Set email body
     * @param string $body
     * @return Provider
     */
    public function setBody($body) {
        $this->data['body'] = $body;
        return $this;
    }


    /**
     * Prepare addresses
     * @param string|User|array $data
     * @return array
     */
    protected function prepareAddresses($data) {
        $addresses = array();
        if(is_array($data)) {
            foreach($data as $address) {
                $address = $this->prepareAddresses($address);
                $addresses = array_merge($addresses, is_array($address) ? $address : array($address));
            }
        } else {
            $address = $this->prepareAddress($data);
            $addresses = array_merge($addresses, is_array($address) ? $address : array($address));
        }
        return $addresses;
    }

    /**
     * Prepare addresses
     * @param string|User|array $data
     * @return array
     */
    protected function prepareAddress($data) {
        if($data instanceof User) {
            $data = array($data->email => $data->name);
        }
        return $data;
    }





} 