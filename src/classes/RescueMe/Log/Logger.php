<?php

    /**
     * File containing: Logger class
     * 
     * @copyright Copyright 2013 {@link http://www.discoos.org DISCO OS Foundation} 
     *
     * @since 27. June 2013
     * 
     * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
     */
    
    namespace RescueMe\Log;
    
    use \RescueMe\User;
    use \Psr\Log\LoggerInterface;
    

    /**
     * Logger class
     */
    class Logger implements LoggerInterface
    {
        /**
         * Logger name
         * @var string
         */
        private $name;


        /**
         * Maximum log level
         * @var string
         */
        private $level;


        /**
         * Maximum log level
         * @var integer
         */
        protected $maximum;
        
        
        /**
         * Truncate to given number of characters, false otherwise.
         * @var integer|boolean
         */
        private $truncate;


        /**
         * Log level priorities
         * @var array
         */
        protected $priority = array
        (
            LogLevel::EMERGENCY => LOG_EMERG,
            LogLevel::ALERT     => LOG_ALERT,
            LogLevel::CRITICAL  => LOG_CRIT,
            LogLevel::ERROR     => LOG_ERR,
            LogLevel::WARNING   => LOG_WARNING,
            LogLevel::NOTICE    => LOG_NOTICE,
            LogLevel::INFO      => LOG_INFO,
            LogLevel::DEBUG     => LOG_DEBUG
        );


        /**
         * Constructor
         *
         * @param string $name Logger name
         * @param string $level Maximum logging level (higher are discarded)
         * @param integer|boolean $truncate Truncate to given number of characters, false otherwise.
         *
         * @throws Exception If level is unknown.
         */
        function __construct($name, $level = LogLevel::INFO, $truncate = false)
        {
            if(defined("\\Psr\\Log\\LogLevel::".strtoupper($level)) === false)
            {
                throw new \Exception("Log level unknown: $level");
            }

            $this->name = $name;
            $this->level = $level;
            $this->maximum = $this->priority[$level];
            $this->truncate = $truncate;

        }


        /**
         * Get logger name
         *
         * @return string
         */
        public function getName()
        {
            return $this->name;
        }


        /**
         * Get maximum log level (higher is filtered)
         *
         * @return string
         */
        public function getLevel()
        {
            return $this->level;
        }


        /**
         * System is unusable.
         *
         * @param string $message Message text
         * @param array $context Context
         *
         * @return void
         */
        public function emergency($message, array $context = array())
        {
            $this->log(LogLevel::EMERGENCY, $message, $context);
        }


        /**
         * Action must be taken immediately.
         *
         * Example: Entire website down, database unavailable, etc. This should
         * trigger the SMS alerts and wake you up.
         *
         * @param string $message Message text
         * @param array $context Context values
         * 
         * @return void
         */
        public function alert($message, array $context = array())
        {
            $this->log(LogLevel::ALERT, $message, $context);
        }


        /**
         * Critical conditions.
         *
         * Example: Application component unavailable, unexpected exception.
         *
         * @param string $message Message text
         * @param array $context Context values
         * 
         * @return void
         */
        public function critical($message, array $context = array())
        {
            $this->log(LogLevel::CRITICAL, $message, $context);
        }


        /**
         * Runtime errors that do not require immediate action but should typically be logged and monitored.
         *
         * @param string $message Message text
         * @param array $context Context values
         *
         * @return void
         */
        public function error($message, array $context = array())
        {
            $this->log(LogLevel::ERROR, $message, $context);
        }


        /**
         * Exceptional occurrences that are not errors.
         *
         * Example: Use of deprecated APIs, poor use of an API, undesirable things
         * that are not necessarily wrong.
         *
         * @param string $message Message text
         * @param array $context Context values
         *
         * @return void
         */
        public function warning($message, array $context = array())
        {
            $this->log(LogLevel::WARNING, $message, $context);
        }


        /**
         * Normal but significant events.
         *
         * @param string $message Message text
         * @param array $context Context values
         *
         * @return void
         */
        public function notice($message, array $context = array())
        {
            $this->log(LogLevel::NOTICE, $message, $context);
        }


        /**
         * Interesting events.
         *
         * Example: User logs in, SQL logs.
         *
         * @param string $message Message text
         * @param array $context Context values
         *
         * @return void
         */
        public function info($message, array $context = array())
        {
            $this->log(LogLevel::INFO, $message, $context);
        }


        /**
         * Detailed debug information.
         *
         * @param string $message Message text
         * @param array $context Context values
         *
         * @return void
         */
        public function debug($message, array $context = array())
        {
            $this->log(LogLevel::DEBUG, $message, $context);
        }


        /**
         * Logs with an arbitrary level.
         *
         * @param string $level Logging level
         * @param string $message Message text
         * @param array $context Log context values
         *
         * @return void
         *
         * @throws \Exception If levels is unknown.
         */
        public function log($level, $message, array $context = array())
        {
            if(defined("\\Psr\\Log\\LogLevel::".strtoupper($level)) === false)
            {
                throw new \Exception("Log level unknown: $level");
            }

            if($this->priority[$level] <= $this->maximum)
            {
                $message = $this->format($message, $context);
                
                $this->write($level, $message, $context);
            }

        }
        
        
        /**
         * Get formatted message text.
         *
         * @param string $message Message text
         * @param array $context Log context values
         *
         * @return mixed
         */
        public function format($message, $context)
        {
            $message = $this->insert($message, $context);
            if($this->truncate)
            {
                $message = Logger::truncate($message, $this->truncate);
            }
            return $message;
        }


        /**
         * Insert context values into the message placeholders.
         *
         * @param string $message Message text Message text
         * @param array $context Log context values
         *
         * @return string
         */
        public function insert($message, $context)
        {
            $replace = array();
            foreach($context as $key => $value)
            {
                if(strtolower($key) === 'exception')
                {
                    if($value instanceof \Exception)
                    {
                        $value = $this::toString($value);
                    }
                }
                $replace['{' . $key . '}'] = $value;
            }

            // Insert context values
            return strtr($message, $replace);

        }
        
        
        /**
         * Write message implementation.
         *
         * @param string $level Log level
         * @param string $message Message text
         * @param array $context Log context values
         *
         * @return void
         */
        private function write($level, $message, $context)
        {
            $user_id = User::currentId();
            
            Logs::log(
                $this->name, 
                $level, 
                $message, 
                $context, 
                isset($user_id) ? $user_id : 0
            );
        }        


        /**
         * Truncates a string to a certain char length, stopping on a word if not specified otherwise.
         *
         * @param string $message Message text
         * @param integer $length Maximum message length
         * @param boolean $anywhere Stop anywhere, or end of word.
         *
         * @return string
         */
        public static function truncate($message, $length, $anywhere = true)
        {
            // Truncates a string to a certain char length, stopping on a word if not specified otherwise.
            $intLength = strlen($message);
            if($intLength > $length)
            {
                // Limit hit!
                $length = min($intLength,$length);
                $message = substr($message, 0, ($length -3));
                if($anywhere)
                {
                    $message .= '...';
                }
                else
                {
                    $message = substr($message,0,strrpos($message,' ')).'...';
                }
            }
            return $message;
        }
        
        
        /**
         * Format exception as string
         *
         * @param \Exception $e Exception instance
         *
         * @return string
         */
        public static function toString(\Exception $e)
        {
            $value  = get_class($e);
            $value .= " with message '" . $e->getMessage() ."'" . PHP_EOL;
            $value .= ' in ' .  $e->getFile();
            $value .= ' at line ' . $e->getLine() . PHP_EOL;
            $value .= 'Stack trace:' . PHP_EOL;
            $value .= $e->getTraceAsString();

            return $value;

        }
        

    }// Logger
