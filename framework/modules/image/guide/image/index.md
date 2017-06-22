# Image

Kohana 3.x provides a simple yet powerful image manipulation module. The [Image] module provides features that allows your application to resize images, crop, rotate, flip and many more.

## Drivers

[Image] module ships with [Image_GD] driver which requires `GD` extension enabled in your PHP installation, and
[Image_Imagick] driver which requires the `imagick` PHP extension. Additional drivers can be created by extending 
the [Image] class.

The [Image_GD] driver is the default. You can change this by providing an `image.default_driver` configuration option
- for example:

~~~
// application/config/image.php
<?php
return array(
    'default_driver' => 'Imagick'
);
~~~

[!!] Older versions of Kohana allowed you to configure the driver with the `Image::$default_driver` static variable in
the bootstrap, an extension class, or elsewhere. That variable is now deprecated and will be ignored if you set a 
config value. 

## Getting Started

Before using the image module, we must enable it first on `APPPATH/bootstrap.php`:

~~~
Kohana::modules(array(
    ...
    'image' => MODPATH.'image',  // Image manipulation
    ...
));
~~~

Next: [Using the image module](using).
