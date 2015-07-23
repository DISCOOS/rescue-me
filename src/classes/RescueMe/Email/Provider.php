<?php
/**
 * File containing: Provider class
 *
 * @copyright Copyright 2014 {@link http://www.discoos.org DISCO OS Foundation}
 *
 * @since 14. April 2014
 *
 * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
 */

namespace RescueMe\Email;
use RescueMe\User;


/**
 * Interface for Email providers
 *
 * @package RescueMe\Email
 */
interface Provider {

    /**
     * Email provider module type
     */
    const TYPE = 'RescueMe\Email\Provider';

    /**
     * Set the email address from which you want the message to be sent
     * @param string|User|array $from
     * @return Provider
     */
    public function setFrom($from);

    /**
     * Set the recipient's email address and name
     * @param string|User|array $to
     * @return Provider
     */
    public function setTo($to);

    /**
     * Set the bulk send mode
     * @param boolean $bulk
     * @return Provider
     */
    public function setBulk($bulk);

    /**
     * Set email subject
     * @param string $subject
     * @return Provider
     */
    public function setSubject($subject);

    /**
     * Set email body
     * @param string $body
     * @return Provider
     */
    public function setBody($body);

    /**
     * Send email to recipients
     * @return boolean|array
     */
    public function send();

} 