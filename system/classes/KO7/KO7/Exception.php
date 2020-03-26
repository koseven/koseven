<?php
/**
 * KO7 exception class. Translates exceptions using the [I18n] class.
 *
 * @package    KO7
 * @category   Exceptions
 *
 * @copyright  (c) 2007-2016  Kohana Team
 * @copyright  (c) since 2016 Koseven Team
 * @license    https://koseven.dev/LICENSE
 */
class KO7_KO7_Exception extends Exception {

    /**
     * @var  array  PHP error code => human readable name
     */
    public static $php_errors = [
        E_ERROR              => 'Fatal Error',
        E_USER_ERROR         => 'User Error',
        E_PARSE              => 'Parse Error',
        E_WARNING            => 'Warning',
        E_USER_WARNING       => 'User Warning',
        E_STRICT             => 'Strict',
        E_NOTICE             => 'Notice',
        E_RECOVERABLE_ERROR  => 'Recoverable Error',
        E_DEPRECATED         => 'Deprecated',
    ];

    /**
     * @var  string  error rendering view
     */
    public static $error_view = 'ko7/error';

    /**
     * @var  string  error view content type
     */
    public static $error_view_content_type = 'text/html';

    /**
     * Creates a new translated exception.
     * 
     * @param   string          $message    error message
     * @param   array           $variables  translation variables
     * @param   integer|string  $code       the exception code
     * @param   Throwable       $previous   Previous throwable
     * @return  void
     */
    public function __construct($message = "", array $variables = NULL, $code = 0, Throwable $previous = NULL)
    {
        // Set the message
        $message = I18n::get([$message, $variables]);

        // Pass the message and integer code to the parent
        parent::__construct($message, (int) $code, $previous);
        
        // @link http://bugs.php.net/39615 Save the unmodified code
        $this->code = $code;
    }

    /**
     * Magic object-to-string method.
     *
     *     echo $exception;
     *
     * @uses    KO7_Exception::text
     * @return  string
     */
    public function __toString()
    {
        return KO7_Exception::text($this);
    }

    /**
     * Inline exception handler, displays the error message, source of the
     * exception, and the stack trace of the error.
     *
     * @uses    KO7_Exception::response
     * @param   Throwable  $t
     * @return  void
     */
    public static function handler(Throwable $t)
    {
        $response = KO7_Exception::_handler($t);

        // Send the response to the browser
        echo $response->send_headers()->body();

        exit(1);
    }

    /**
     * Exception handler, logs the exception and generates a Response object
     * for display.
     *
     * @uses    KO7_Exception::response
     * @param   Throwable  $t
     * @return  Response
     */
    public static function _handler(Throwable $t)
    {
        try
        {
            // Log the exception
            KO7_Exception::log($t);

            // Generate the response
            $response = KO7_Exception::response($t);

            return $response;
        }
        catch (Exception $e)
        {
            /**
             * Things are going *really* badly for us, We now have no choice
             * but to bail. Hard.
             */
            // Clean the output buffer if one exists
            ob_get_level() AND ob_clean();

            // Set the Status code to 500, and Content-Type to text/plain.
            header('Content-Type: text/plain; charset='.KO7::$charset, TRUE, 500);

            echo KO7_Exception::text($e);

            exit(1);
        }
    }

    /**
     * Logs an exception.
     *
     * @uses    KO7_Exception::text
     * @param   Throwable  $t
     * @param   int        $level
     * @return  void
     */
    public static function log(Throwable $t, $level = Log::EMERGENCY)
    {
        if (is_object(KO7::$log))
        {
            // Create a text version of the exception
            $error = KO7_Exception::text($t);

            // Add this exception to the log
            KO7::$log->add($level, $error, NULL, ['exception' => $t]);

            // Make sure the logs are written
            KO7::$log->write();
        }
    }

    /**
     * Get a single line of text representing the exception:
     *
     * Error [ Code ]: Message ~ File [ Line ]
     *
     * @param   Throwable  $t
     * @return  string
     */
    public static function text(Throwable $t)
    {
        return sprintf('%s [ %s ]: %s ~ %s [ %d ]',
            get_class($t), $t->getCode(), strip_tags($t->getMessage()), Debug::path($t->getFile()), $t->getLine());
    }

    /**
     * Get a Response object representing the exception
     *
     * @uses    KO7_Exception::text
     * @param   Throwable  $t
     * @return  Response
     */
    public static function response(Throwable $t)
    {
        try
        {
            // Get the exception information
            $code = $t->getCode();
            $trace = $t->getTrace();

            /**
             * HTTP_Exceptions are constructed in the HTTP_Exception::factory()
             * method. We need to remove that entry from the trace and overwrite
             * the variables from above.
             */
            if ($t instanceof HTTP_Exception AND $trace[0]['function'] == 'factory')
            {
                extract(array_shift($trace));
            }


            if ($t instanceof ErrorException)
            {
                /**
                 * If XDebug is installed, and this is a fatal error,
                 * use XDebug to generate the stack trace
                 */
                if (function_exists('xdebug_get_function_stack') AND $code == E_ERROR)
                {
                    $trace = array_slice(array_reverse(xdebug_get_function_stack()), 4);

                    foreach ($trace as & $frame)
                    {
                        /**
                         * XDebug pre 2.1.1 doesn't currently set the call type key
                         * http://bugs.xdebug.org/view.php?id=695
                         */
                        if ( ! isset($frame['type']))
                        {
                            $frame['type'] = '??';
                        }

                        // Xdebug returns the words 'dynamic' and 'static' instead of using '->' and '::' symbols
                        if ('dynamic' === $frame['type'])
                        {
                            $frame['type'] = '->';
                        }
                        elseif ('static' === $frame['type'])
                        {
                            $frame['type'] = '::';
                        }

                        // XDebug also has a different name for the parameters array
                        if (isset($frame['params']) AND ! isset($frame['args']))
                        {
                            $frame['args'] = $frame['params'];
                        }
                    }
                }

                if (isset(KO7_Exception::$php_errors[$code]))
                {
                    // Use the human-readable error name
                    $code = KO7_Exception::$php_errors[$code];
                }
            }

            /**
             * The stack trace becomes unmanageable inside PHPUnit.
             *
             * The error view ends up several GB in size, taking
             * serveral minutes to render.
             */
            if (
                defined('PHPUnit_MAIN_METHOD')
                OR
                defined('PHPUNIT_COMPOSER_INSTALL')
                OR
                defined('__PHPUNIT_PHAR__')
            )
            {
                $trace = array_slice($trace, 0, 2);
            }

            // Instantiate the error view.
            $view = View::factory(KO7_Exception::$error_view, get_defined_vars());

            // Prepare the response object.
            $response = Response::factory();

            // Set the response status
            $response->status(($t instanceof HTTP_Exception) ? $t->getCode() : 500);

            // Set the response headers
            $response->headers('Content-Type', KO7_Exception::$error_view_content_type.'; charset='.KO7::$charset);

            // Set the response body
            $response->body($view->render());
        }
        catch (Exception $e)
        {
            /**
             * Things are going badly for us, Lets try to keep things under control by
             * generating a simpler response object.
             */
            $response = Response::factory();
            $response->status(500);
            $response->headers('Content-Type', 'text/plain');
            $response->body(KO7_Exception::text($e));
        }

        return $response;
    }
}
