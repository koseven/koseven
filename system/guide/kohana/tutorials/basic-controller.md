# Example of basic controller for application

## Auto render templates for action and layout
Extends Controller_Template: action template wrapped in layout/theme template.
~~~
<?php
abstract class Controller_Base extends Controller_Template {
    /**
     * @var  string|View  Page template, wrapper for action content.
     */
    public $template = 'layout';
    /**
     * @var  string|View  Action content template.
     */
    public $action_template;
    /**
     * Init View for action template and bind as 'action_content' in layout template.
     * @return  void
     */
    public function init_action_template()
    {
        if ( ! $this->action_template)
        {
            // Auto set name of action template based on current controller and action.
            $this->action_template = [
                $this->request->controller(),
                $this->request->action()
            ];
            if ($this->request->directory())
            {
                array_unshift($this->action_template, $this->request->directory());
            }
            $this->action_template = implode(DIRECTORY_SEPARATOR, $this->action_template);
        }
        // Create View for action template.
        $this->action_template = new View($this->action_template);
        // Wrap action template in layout.
        $this->template->bind('main_content', $this->action_template);
    }
    /**
     * Automatically executed before the controller action.
     * @return  void
     */
    public function before()
    {
        parent::before();
        
        // Set action template after parent code.
        if ($this->auto_render)
        {
            $this->init_action_template();
        }
    }
}
~~~
Example of `layout.php`:
~~~
<!DOCTYPE html>
<html>
    <head>
      ...
    </head>
    <body>
      <header>
        ...
      </header>
      <aside>
        ...
      </aside>
      <main>
        <!-- Render action template -->
        <?=$main_content->render()?>
      </main>
      <footer>
        ...
      </footer>
    </body>
</html>
~~~

## Check user authorization and RBAC authentication
Used Auth module. Set `$check_auth` as `TRUE` for auto check auth. 
Set `$auth_roles` as `['login', 'admin']` for backend controllers.
~~~
/**
 * @var  bool  Check user auth?
 */
public $check_auth = FALSE;
/**
 * @var  array  List the roles required to access.
 */
public $auth_roles = ['login'];
/**
 * Check user authentication and authorization.
 * @return  void
 */
public function check_auth()
{
    $auth = Auth::instance();
    if ( ! $auth->get_user())
    {
        throw new HTTP_Exception_401('Unauthorized user');
    }
    elseif ( ! $auth->logged_in($this->auth_roles))
    {
        throw new HTTP_Exception_403('Access forbidden');
    }
}
/**
 * Automatically executed before the controller action.
 * @return  void
 */
public function before()
{
    // Check auth at first.
    if ($this->check_auth)
    {
        $this->check_auth();
    }

    parent::before();

    if ($this->auto_render)
    {
        $this->init_action_template();
    }
}
~~~

## Load configuration
Set `$config` as `['application_config', 'controller_config']` or `['application_config', 'controller_config', 'action_config']`.
~~~
/**
 * @var  array  Auto loaded configuration.
 */
public $config = [];
/**
 * Load controller configuration from config parts.
 *
 * @return  void
 */
public function init_config()
{
    $config = [];
    foreach ($this->config as $name)
    {
        // Load and merge config parts.
        $config = array_merge($config, (array) Kohana::config($name, TRUE));
    }
    $this->config = $config;
}
/**
 * Automatically executed before the controller action.
 * @return  void
 */
public function before()
{
    if ($this->check_auth)
    {
        $this->check_auth();
    }
    parent::before();
    if ($this->auto_render)
    {
        $this->init_action_template();
    }
    
    // Set config at end.
    if ($this->config)
    {
        $this->init_config();
    }
}
~~~
Example of application config:
~~~
[
  'meta' => [
    'title' => 'Application title',
    'description' => 'Application description',
  ],
]
~~~
Example of controller config:
~~~
[
  'meta' => [
    'title' => 'Controller title',
  ],
]
~~~
Combined config in `$config`:
~~~
[
  'meta' => [
    'title' => 'Controller title',
    'description' => 'Application description',
  ],
]
~~~
