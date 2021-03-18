<?php

/**
 * Class Properties
 *
 * @package    Kohana
 * @category   Cookie
 * @author     Kohana Team
 * @copyright  (c) Kohana Team
 * @license    https://koseven.ga/LICENSE.md
 */
final class Kohana_Cookie_Properties
{
    /**
     * Name of the cookie
     */
    const NAME = 'name';

    /**
     * Value of the cookie
     */
    const VALUE = 'value';

    /**
     * The time the cookie expires. This is a Unix timestamp so is in number of seconds since the epoch.
     * In other words, you'll most likely set this with the time() function plus the number of seconds before
     * you want it to expire. Or you might use mktime(). time()+60*60*24*30 will set the cookie to expire in 30 days.
     * If set to 0, or omitted, the cookie will expire at the end of the session (when the browser closes).
     */
    const EXPIRES = 'expires';

    /**
     * The path on the server in which the cookie will be available on.
     * If set to '/', the cookie will be available within the entire domain. If set to '/foo/',
     * the cookie will only be available within the /foo/ directory and all sub-directories such as /foo/bar/ of
     * domain. The default value is the current directory that the cookie is being set in.
     */
    const PATH = 'path';

    /**
     * The (sub)domain that the cookie is available to. Setting this to a subdomain (such as 'www.example.com')
     * will make the cookie available to that subdomain and all other sub-domains of it (i.e. w2.www.example.com).
     * To make the cookie available to the whole domain (including all subdomains of it), simply set the value to
     * the domain name ('example.com', in this case).
     */
    const DOMAIN = 'domain';

    /**
     * Indicates that the cookie should only be transmitted over a secure HTTPS connection from the client.
     * When set to TRUE, the cookie will only be set if a secure connection exists. On the server-side,
     * it's on the programmer to send this kind of cookie only on secure connection
     * (e.g. with respect to $_SERVER["HTTPS"]).
     */
    const SECURE = 'secure';

    /**
     * When TRUE the cookie will be made accessible only through the HTTP protocol.
     * This means that the cookie won't be accessible by scripting languages, such as JavaScript.
     * It has been suggested that this setting can effectively help to reduce identity theft through XSS attacks
     * (although it is not supported by all browsers), but that claim is often disputed.
     * Added in PHP 5.2.0. TRUE or FALSE
     */
    const HTTP_ONLY = 'httponly';

    /**
     * The SameSite attribute of the Set-Cookie HTTP response header allows you to declare if your cookie
     * should be restricted to a first-party or same-site context.
     */
    const SAME_SITE = 'samesite';
}