<?php namespace Milkyway\SS\Core;

/**
 * Milkyway Multimedia
 * CookieJar.php
 *
 * @package milkyway-multimedia/ss-mwm
 * @author  Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */

use CookieJar as Original;

class CookieJar extends Original
{
    /**
     * @inheritdoc
     */
    protected function outputCookie(
        $name,
        $value,
        $expiry = 90,
        $path = null,
        $domain = null,
        $secure = false,
        $httpOnly = true
    ) {
        return parent::outputCookie(
            $name,
            $value,
            $expiry,
            $this->updateConfig('cookie_path', $path),
            $this->updateConfig('cookie_domain', $domain),
            $secure,
            $httpOnly
        );
    }

    /**
     * Allow setting cookie domain
     * @param string $key
     * @param mixed $value
     * @param mixed $default
     * @return mixed
     */
    protected function updateConfig($key, $value = null, $default = null)
    {
        $value = $value ?: singleton('env')->get('Cookie.'.$key);

        if (!$value && !singleton('env')->get('Cookie.dont_use_same_config_as_sessions')) {
            $value = singleton('env')->get('Session.'.$key);
        }

        if(!$value && $default) {
            $value = $default;
        }

        return $value;
    }
}