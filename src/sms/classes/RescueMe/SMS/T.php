<?php

namespace RescueMe\SMS;

use RescueMe\Locale;

class T {

    /**
     *
     */
    const ALERT_SMS = 1;

    /**
     *
     */
    const ALERT_SMS_NOT_SENT = 2;

    /**
     *
     */
    const ALERT_SMS_COARSE_LOCATION = 3;

    /**
     *
     */
    const ALERT_SMS_LOCATION_UPDATE = 4;


    /**
     * Get localized message from message id in SMS domain.
     *
     * @param integer|string $msgid Message id
     * @param string $locale Message locale. Pass <em>null</em> for current locale (default).
     *
     * @return string
     */
    public static function _($msgid, $locale = null) {

        if(is_null($locale)) {
            $locale = Locale::getCurrentLocale();
        }

        if(is_numeric($msgid)) {
            switch($msgid) {
                case T::ALERT_SMS:
                    return T_locale('We are searching for you! Click on this link so we can locate you: %LINK%', $locale, DOMAIN_SMS);
                case T::ALERT_SMS_NOT_SENT:
                    return T_locale('Warning: SMS not sent to #m_name', $locale, DOMAIN_SMS);
                case T::ALERT_SMS_COARSE_LOCATION:
                    return T_locale('If you have GPS on your phone, we recommend that you enable this. In most cases you will find this under Settings -> General, or Settings -> Location.', $locale, DOMAIN_SMS);
                case T::ALERT_SMS_LOCATION_UPDATE:
                    return T_locale('Received location of #m_name: #pos (+/-#acc m)! %1$s', $locale, DOMAIN_SMS);
                default:
                    break;
            }
        }

        return T_locale($msgid, $locale, DOMAIN_SMS);
    }

}