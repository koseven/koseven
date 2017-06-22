<?php

// Get the latest logo contents
$data = base64_encode(file_get_contents('http://kohanaframework.org/media/img/kohana.png'));

// Create the logo file
file_put_contents('logo.php', "<?php
/**
 * Kohana Logo, base64_encoded PNG
 * 
 * @copyright  (c) Kohana Team
 * @license    https://koseven.ga/LICENSE.md
 */
return array('mime' => 'image/png', 'data' => '{$data}'); ?>");