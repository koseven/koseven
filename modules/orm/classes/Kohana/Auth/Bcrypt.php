<?php

/**
 * Bcrypt Auth driver.
 * @package    Kohana/Auth
 * @author     Kohana Team
 * @copyright  (c) Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Kohana_Auth_Bcrypt extends Auth {

    public function __construct($config = array())
    {
        if ( ! isset($config['cost']) OR ! is_numeric($config['cost']) OR $config['cost'] < 10)
        {
            throw new Kohana_Exception(__CLASS__ . ' cost parameter must be set and must be integer >= 10');
        }
        parent::__construct($config);
    }

    /**
     * Logs a user in, based on the authautologin cookie.
     *
     * @return  mixed
     */
    public function auto_login()
    {
        if ($token = Cookie::get('authautologin'))
        {
            // Load the token and user
            $token = ORM::factory('User_Token', array('token' => $token));

            if ($token->loaded() AND $token->user->loaded())
            {
                if ($token->user_agent === hash('sha256', Request::$user_agent))
                {
                    // Save the token to create a new unique token
                    $token->save();

                    // Set the new token
                    Cookie::set('authautologin', $token->token, $token->expires - time());

                    // Complete the login with the found data
                    $this->complete_login($token->user);

                    // Automatic login was successful
                    return $token->user;
                }

                // Token is invalid
                $token->delete();
            }
        }

        return FALSE;
    }

    /**
     * Gets the currently logged in user from the session (with auto_login check).
     * Returns $default if no user is currently logged in.
     *
     * @param   mixed    $default to return in case user isn't logged in
     * @return  mixed
     */
    public function get_user($default = NULL)
    {
        $user = parent::get_user($default);

        if ($user === $default)
        {
            // check for "remembered" login
            if (($user = $this->auto_login()) === FALSE)
                return $default;
        }

        return $user;
    }

    /**
     * Log a user out and remove any autologin cookies.
     *
     * @param   boolean  $destroy     completely destroy the session
     * @param	boolean  $logout_all  remove all tokens for user
     * @return  boolean
     */
    public function logout($destroy = FALSE, $logout_all = FALSE)
    {
        // Set by force_login()
        $this->_session->delete('auth_forced');

        if ($token = Cookie::get('authautologin'))
        {
            // Delete the autologin cookie to prevent re-login
            Cookie::delete('authautologin');

            // Clear the autologin token from the database
            $token = ORM::factory('User_Token', array('token' => $token));

            if ($token->loaded() AND $logout_all)
            {
                // Delete all user tokens. This isn't the most elegant solution but does the job
                $tokens = ORM::factory('User_Token')->where('user_id', '=', $token->user_id)->find_all();

                foreach ($tokens as $_token)
                {
                    $_token->delete();
                }
            }
            elseif ($token->loaded())
            {
                $token->delete();
            }
        }

        return parent::logout($destroy);
    }

    /**
     * Performs login on user
     * @param string $username Username
     * @param string $password Password
     * @param bool $remember Remembers login
     * @return boolean
     * @throws Kohana_Exception
     */
    protected function _login($username, $password, $remember = FALSE)
    {
        if ( ! is_string($username) OR ! is_string($password) OR ! is_bool($remember))
        {
            throw new Kohana_Exception('Username and password must be strings, remember must be bool');
        }

        // Load the user
        $user = ORM::factory('User');
        $user->where($user->unique_key($username), '=', $username)->find();

        if ($user->loaded() AND $user->has('roles', ORM::factory('Role', array('name' => 'login'))) AND password_verify($password, $user->password))
        {
            if ($remember === TRUE)
            {
                // Token data
                $data = array(
                    'user_id' => $user->pk(),
                    'expires' => time() + $this->_config['lifetime'],
                    'user_agent' => hash('sha256', Request::$user_agent),
                );

                // Create a new autologin token
                $token = ORM::factory('User_Token')
                        ->values($data)
                        ->create();

                // Set the autologin cookie
                Cookie::set('authautologin', $token->token, $this->_config['lifetime']);
            }

            $this->complete_login($user);
            $user->complete_login();

            if (password_needs_rehash($user->password, PASSWORD_BCRYPT, array('cost' => $this->_config['cost'])))
            {
                $user->password = $password;
                $user->save();
            }

            return TRUE;
        }
        
        return FALSE;
    }

    /**
	 * Compare password with original (hashed). Works for current (logged in) user
	 *
	 * @param   string  $password
	 * @return  boolean
	 */
    public function check_password($password)
    {
        $user = $this->get_user();

        if ( ! $user)
        {
            return false;
        }

        return password_verify($password, $user->password);
    }

    /**
     * Get the stored password for a username.
     *
     * @param   mixed   $user  username string, or user ORM object
     * @return  string
     */
    public function password($user)
    {
        if ( ! is_object($user))
        {
            $username = $user;

            // Load the user
            $user = ORM::factory('User');
            $user->where($user->unique_key($username), '=', $username)->find();
        }

        return $user->password;
    }

    /**
     * Forces a user to be logged in, without specifying a password.
     *
     * @param   mixed    $user                    username string, or user ORM object
     * @param   boolean  $mark_session_as_forced  mark the session as forced
     * @return  boolean
     */
    public function force_login($user, $mark_session_as_forced = FALSE)
    {
        if ( ! is_object($user))
        {
            $username = $user;

            // Load the user
            $user = ORM::factory('User');
            $user->where($user->unique_key($username), '=', $username)->find();
        }

        if ($mark_session_as_forced === TRUE)
        {
            // Mark the session as forced, to prevent users from changing account information
            $this->_session->set('auth_forced', TRUE);
        }

        // Run the standard completion
        $this->complete_login($user);
    }

    /**
     * @inheritdoc
     */
    public function hash($str)
    {
        return password_hash($str, PASSWORD_BCRYPT, [
            'cost' => $this->_config['cost']
        ]);
    }

}
