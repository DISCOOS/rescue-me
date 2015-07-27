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

use RescueMe\Configuration;

/**
 * SMTP Mailer provider
 * @package RescueMe\Email
 */
class SMTP extends AbstractProvider {

    /**
     * SMPT Email provider module type
     */
    const TYPE = 'RescueMe\Email\SMTP';

    /**
     * Sending user id
     * @var int
     */
    private $userId;

    /**
     * Constructor
     * @param int $userId
     * @param string $host
     * @param string $port
     * @param string $encryption
     * @param string $username
     * @param string $password
     */
    public function __construct($userId=0, $host='', $port='25', $encryption=null, $username='', $password='') {
        
        parent::__construct($this->newConfig(
                $host,
                $port,
                $encryption,
                $username,
                $password
            ));
        
        $this->userId = $userId;

    }

    /**
     * Check if SMPT is supported by host system.
     * @return bool
     */
    public function isSupported()
    {
        return function_exists('proc_open');
    }


    /**
     * Create configuration object
     * @param string $host
     * @param string $port
     * @param string $encryption
     * @param string $username
     * @param string $password
     * @return Configuration
     */
    private function newConfig($host='', $port='25', $encryption=null, $username='', $password='') {
        return new Configuration(
            array(
                "host" => $host,
                "port" => $port,
                "encryption" => $encryption,
                "username" => $username,
                "password" => $password
            ),
            array(
                "host" => T_('Host'),
                "port" => T_('Port'),
                "encryption" => T_('Encryption'),
                "username" => T_('Username'),
                "password" => T_('Password')
            ),
            array(
                "host",
                "port",
                "username",
                "password"
            )
        );
    }

    /**
     * Validate SMTP transport
     * @param array $params
     * @return bool
     */
    protected function validateParameters($params)
    {
        // Validate encryption support
        $encryption = $this->config->get('encryption');
        if(is_null($encryption) === false) {
            if(in_array($encryption, stream_get_transports()) === false) {
                return false;
            }
        }

        // Create the Transport
        $transport = $this->newTransport();

        // Start transport and validate connection
        try {
            $transport->start();
        } catch (\Swift_TransportException $e) {
            $this->fatal($e->getMessage());
        }

        $valid = $transport->isStarted();

        // Cleanup
        $transport->stop();

        return $valid;

    }

    /**
     * Create new SMTP transport instance
     * @return \Swift_SmtpTransport
     */
    private function newTransport() {
        return \Swift_SmtpTransport::newInstance(
            $this->config->get('host'),
            $this->config->get('port'),
            $this->config->get('encryption'))
            ->setUsername($this->config->get('username'))
            ->setPassword($this->config->get('password'));
    }


    /**
     * Send email to recipients
     * @return boolean|array
     */
    public function send() {

        $failed = array();
        
        // Create the Transport
        $transport = $this->newTransport();

        // Create the Mailer using your created Transport
        $mailer = \Swift_Mailer::newInstance($transport);

        // Create a message
        $message = \Swift_Message::newInstance(isset_get($this->data, 'subject'));

        $from = $this->prepareAddress(isset_get($this->data,'from'));
        $message->setFrom($from)
                ->setBody(isset_get($this->data,'body'), 'text/html');

        $to = $this->prepareAddresses(isset_get($this->data,'to'));

        if(isset_get($this->data,'bulk',false) === true) {
            foreach($to as $address) {
                $message->setTo($address);
                $mailer->send($message, $failed);
            }
        } else {
            $message->setTo($to);
            $mailer->send($message, $failed);
        }

        return empty($failed) ? true : $failed;
    }

}