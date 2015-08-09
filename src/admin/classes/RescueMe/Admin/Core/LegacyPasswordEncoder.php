<?php
/**
 * File containing: Password encoder controller class
 *
 * @copyright Copyright 2015 {@link http://www.discoos.org DISCO OS Foundation}
 *
 * @since 29. July 2015
 *
 * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
 */

namespace RescueMe\Admin\Core;


use Symfony\Component\Security\Core\Encoder\BasePasswordEncoder;

/**
 * Legacy RescueMe password encoder
 *
 * @package RescueMe\Admin\Core
 */
class LegacyPasswordEncoder extends BasePasswordEncoder {

    /**
     * Encodes the raw password.
     *
     * @param string $raw The password to encode
     * @param string $salt The salt
     *
     * @return string The encoded password
     */
    public function encodePassword($raw, $salt) {
        return sha1($salt . $raw . '^[]|2"!#');
    }

    /**
     * Checks a raw password against an encoded password.
     *
     * @param string $encoded An encoded password
     * @param string $raw A raw password
     * @param string $salt The salt
     *
     * @return bool true if the password is valid, false otherwise
     */
    public function isPasswordValid($encoded, $raw, $salt) {
        return $this->comparePasswords($encoded, $this->encodePassword($raw, $salt));
    }
}