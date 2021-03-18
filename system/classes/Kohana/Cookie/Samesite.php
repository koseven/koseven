<?php

/**
 * Class Samesite
 *
 * @package    Kohana
 * @category   Cookie
 * @author     Kohana Team
 * @copyright  (c) Kohana Team
 * @license    https://koseven.ga/LICENSE.md
 */
final class Kohana_Cookie_Samesite
{
    /**
     * Cookies are allowed to be sent with top-level navigations and will be sent along with GET request
     * initiated by third party website. This is the default value in modern browsers.
     */
    const LAX = 'Lax';

    /**
     * Cookies will only be sent in a first-party context and not be sent along with requests initiated
     * by third party websites.
     */
    const STRICT = 'Strict';

    /**
     * None used to be the default value, but recent browser versions made Lax the default value to have reasonably
     * robust defense against some classes of cross-site request forgery (CSRF) attacks.
     *
     * None requires the Secure attribute in latest browser versions.
     */
    const NONE = 'None';
}