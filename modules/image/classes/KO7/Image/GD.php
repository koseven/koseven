<?php
/**
 * Support for image manipulation using [GD](http://php.net/GD).
 *
 * @package        KO7/Image/Driver
 *
 * @copyright  (c) 2007-2016  Kohana Team
 * @copyright  (c) since 2016 Koseven Team
 * @license        https://koseven.dev/LICENSE
 */
class KO7_Image_GD extends Image {

	/**
	 * GD rotate function
	 */
	const IMAGEROTATE = 'imagerotate';

	/**
	 * GD convolution function
	 */
	const IMAGECONVOLUTION = 'imageconvolution';

	/**
	 * GD filter function
	 */
	const IMAGEFILTER = 'imagefilter';

	/**
	 * GD layer-effect function
	 */
	const IMAGELAYEREFFECT = 'imagelayereffect';

	/**
	 * Holds available GD functions
	 * @var array
	 */
	protected static $_available_functions = [];

	/**
	 * Temporary Image Resource
	 * @var mixed
	 */
	protected $_image;

	/**
	 * GD has different function names for different image types.
	 * This one holds the current function, which is needed to open the image.
	 *
	 * @var string
	 */
	protected $_create_function;

	/**
	 * Checks if GD is enabled and verify that key methods exist, some of which require GD to
	 * be bundled with PHP.
	 *
	 * @return  boolean
	 */
	public static function check(): bool
	{
		// Check if gd is loaded correctly
		if ( ! extension_loaded('gd'))
		{
			return FALSE; // @codeCoverageIgnore
		}

		// Check available functions
		$functions = [
			Image_GD::IMAGEROTATE,
			Image_GD::IMAGECONVOLUTION,
			Image_GD::IMAGEFILTER,
			Image_GD::IMAGELAYEREFFECT
		];

		foreach ($functions as $function)
		{
			Image_GD::$_available_functions[$function] = function_exists($function);
		}

		if ( ! version_compare(GD_VERSION, '2.0', '>='))
		{
			return FALSE; // @codeCoverageIgnore
		}

		return TRUE;
	}

	/**
	 * KO7_Image_GD constructor.
	 *
	 * @param string $file Path to image file
	 *
	 * @throws  Image_Exception
	 */
	public function __construct($file)
	{
		// Call parent constructor
		parent::__construct($file);

		// Set the image creation function name
		$ext = image_type_to_extension($this->type, FALSE);

		// jpe and jpg are both valid extensions for jpeg
		if (in_array($ext, [
			'jpe',
			'jpg',
		]))
		{
			$ext = 'jpeg';
		}

		$create = 'imagecreatefrom' . strtolower($ext);

		// Check if imagecreate function exists
		// @codeCoverageIgnoreStart
		if ( ! function_exists($create))
		{
			throw new Image_Exception(
				'Installed GD does not support ":ext" images',
				[':type' => $ext]
			);
		}
		// @codeCoverageIgnoreEnd

		// Save function for future use
		$this->_create_function = $create;

		// Save filename for lazy loading
		$this->_image = $this->file;
	}

	/**
	 * Destroys the loaded image to free up resources.
	 */
	public function __destruct()
	{
		if (is_resource($this->_image))
		{
			// Free all resources
			imagedestroy($this->_image);
		}
	}

	/**
	 * Loads an image into GD.
	 */
	protected function _load_image() : void
	{
		if ( ! is_resource($this->_image))
		{
			// Gets create function
			$create = $this->_create_function;

			// Open the temporary image
			$this->_image = $create($this->file);

			// Preserve transparency when saving
			imagesavealpha($this->_image, TRUE);
		}
	}

	/**
	 * Resize function for GD
	 *
	 * @param int $width	Width to resize to
	 * @param int $height	Height to resize to
	 *
	 * @return bool
	 */
	protected function _do_resize($width, $height): bool
	{
		// Pre-Size width and height
		$pre_width = $this->width;
		$pre_height = $this->height;

		// Loads image if not yet loaded
		$this->_load_image();

		// Test if we can do a resize without re-sampling to speed up the final resize
		if ($width > ($this->width / 2) && $height > ($this->height / 2))
		{
			// Create the temporary image to copy to
			$image = $this->_create($pre_width, $pre_height);

			if (imagecopyresized($image, $this->_image, 0, 0, 0, 0, $pre_width, $pre_height, $this->width, $this->height))
			{
				// Swap the new image for the old one
				imagedestroy($this->_image);
				$this->_image = $image;
			}
		}

		// Create the temporary image to copy to
		$image = $this->_create($width, $height);

		// Execute the resize
		if ($success = imagecopyresampled($image, $this->_image, 0, 0, 0, 0, $width, $height, $pre_width, $pre_height))
		{
			// Swap the new image for the old one
			imagedestroy($this->_image);
			$this->_image = $image;

			// Reset the width and height
			$this->width = imagesx($image);
			$this->height = imagesy($image);
		}

		return $success;
	}

	/**
	 * Crop function for GD
	 *
	 * @param integer $width    New width
	 * @param integer $height   New height
	 * @param integer $offset_x Offset from the left
	 * @param integer $offset_y Offset from the top
	 *
	 * @return  bool
	 */
	protected function _do_crop($width, $height, $offset_x, $offset_y): bool
	{
		// Support for negative offsets
		$dest_width = $width;
		$dest_height = $height;
		if ($offset_x < 0)
		{
			$dest_width = $width + $offset_x;
			$offset_x = 0;
		}
		if ($offset_y < 0)
		{
			$dest_height = $height + $offset_y;
			$offset_y = 0;
		}

		// Create the temporary image to copy to
		$image = $this->_create($dest_width, $dest_height);

		// Loads image if not yet loaded
		$this->_load_image();

		// Execute the crop
		if ($success = imagecopyresampled($image, $this->_image, 0, 0, $offset_x, $offset_y, $width, $height, $width, $height))
		{
			// Swap the new image for the old one
			imagedestroy($this->_image);
			$this->_image = $image;

			// Reset the width and height
			$this->width = imagesx($image);
			$this->height = imagesy($image);
		}

		return $success;
	}

	/**
	 * Rotation function for GD
	 *
	 * @param int $degrees	Degrees to rotate
	 *
	 * @throws Image_Exception
	 *
	 * @return bool
	 */
	protected function _do_rotate($degrees): bool
	{
		// Check if function is available in GD
		if (empty(Image_GD::$_available_functions[Image_GD::IMAGEROTATE]))
		{
			throw new Image_Exception('This method requires "imagerotate", which is only available in the bundled version of GD'); // @codeCoverageIgnore
		}

		// Loads image if not yet loaded
		$this->_load_image();

		// Transparent black will be used as the background for the uncovered region
		$transparent = imagecolorallocatealpha($this->_image, 0, 0, 0, 127);

		// Rotate, setting the transparent color
		$image = imagerotate($this->_image, 360 - $degrees, $transparent);

		// Save the alpha of the rotated image
		imagesavealpha($image, TRUE);

		// Get the width and height of the rotated image
		$width = imagesx($image);
		$height = imagesy($image);

		if ($success = imagecopymerge($this->_image, $image, 0, 0, 0, 0, $width, $height, 100))
		{
			// Swap the new image for the old one
			imagedestroy($this->_image);
			$this->_image = $image;

			// Reset the width and height
			$this->width = $width;
			$this->height = $height;
		}

		return $success;
	}

	/**
	 * Flip function for GD
	 *
	 * @param int $direction Direction to flip
	 *
	 * @return bool
	 */
	protected function _do_flip($direction): bool
	{
		// Flip was successful
		$success = FALSE;

		// Create the flipped image
		$flipped = $this->_create($this->width, $this->height);

		// Loads image if not yet loaded
		$this->_load_image();

		if ($direction === Image::HORIZONTAL)
		{
			for ($x = 0; $x < $this->width; $x++)
			{
				// Flip each row from top to bottom
				$success = imagecopy($flipped, $this->_image, $x, 0, $this->width - $x - 1, 0, 1, $this->height);
			}
		}
		else
		{
			for ($y = 0; $y < $this->height; $y++)
			{
				// Flip each column from left to right
				$success = imagecopy($flipped, $this->_image, 0, $y, 0, $this->height - $y - 1, $this->width, 1);
			}
		}

		// Swap the new image for the old one
		imagedestroy($this->_image);
		$this->_image = $flipped;

		// Reset the width and height
		$this->width = imagesx($flipped);
		$this->height = imagesy($flipped);

		return $success;
	}

	/**
	 * Sharpen function for GD
	 *
	 * @param int $amount	Amount to sharpen the iamge
	 *
	 * @throws Image_Exception
	 *
	 * @return bool
	 */
	protected function _do_sharpen($amount): bool
	{
		// Check if function is available in GD
		if (empty(Image_GD::$_available_functions[Image_GD::IMAGECONVOLUTION]))
		{
			throw new Image_Exception('This method requires "imageconvolution", which is only available in the bundled version of GD'); // @codeCoverageIgnore
		}

		// Loads image if not yet loaded
		$this->_load_image();

		// Amount should be in the range of 18-10
		$amount = round(abs(-18 + ($amount * 0.08)), 2);

		// Gaussian blur matrix
		$matrix = [[-1, -1, -1], [-1, $amount, -1], [-1, -1, -1],];

		// Perform the sharpen
		if ($success = imageconvolution($this->_image, $matrix, $amount - 8, 0))
		{
			// Reset the width and height
			$this->width = imagesx($this->_image);
			$this->height = imagesy($this->_image);
		}

		return $success;
	}

	/**
	 * Reflection function for GD
	 *
	 * @param int  $height	 Reflection height
	 * @param int  $opacity	 Reflection opacity
	 * @param bool $fade_in	 TRUE to fade out, FALSE to fade in
	 *
	 * @throws Image_Exception
	 *
	 * @return bool
	 */
	protected function _do_reflection($height, $opacity, $fade_in): bool
	{
		// Check if function is available in GD
		if (empty(Image_GD::$_available_functions[Image_GD::IMAGEFILTER]))
		{
			throw new Image_Exception('This method requires "imagefilter", which is only available in the bundled version of GD'); // @codeCoverageIgnore
		}

		// Loads image if not yet loaded
		$this->_load_image();

		// Convert an opacity range of 0-100 to 127-0
		$opacity = round(abs(($opacity * 127 / 100) - 127));

		if ($opacity < 127)
		{
			// Calculate the opacity stepping
			$stepping = (127 - $opacity) / $height;
		}
		else
		{
			// Avoid a "divide by zero" error
			$stepping = 127 / $height;
		}

		// Create the reflection image
		$reflection = $this->_create($this->width, $this->height + $height);

		// Copy the image to the reflection
		$success = imagecopy($reflection, $this->_image, 0, 0, 0, 0, $this->width, $this->height);

		for ($offset = 0; $height >= $offset; $offset++)
		{
			// Read the next line down
			$src_y = $this->height - $offset - 1;

			// Place the line at the bottom of the reflection
			$dst_y = $this->height + $offset;

			if ($fade_in === TRUE)
			{
				// Start with the most transparent line first
				$dst_opacity = round($opacity + ($stepping * ($height - $offset)));
			}
			else
			{
				// Start with the most opaque line first
				$dst_opacity = round($opacity + ($stepping * $offset));
			}

			// Create a single line of the image
			$line = $this->_create($this->width, 1);

			// Copy a single line from the current image into the line
			imagecopy($line, $this->_image, 0, 0, 0, $src_y, $this->width, 1);

			// Colorize the line to add the correct alpha level
			imagefilter($line, IMG_FILTER_COLORIZE, 0, 0, 0, $dst_opacity);

			// Copy a the line into the reflection
			imagecopy($reflection, $line, 0, $dst_y, 0, 0, $this->width, 1);
		}

		// Swap the new image for the old one
		imagedestroy($this->_image);
		$this->_image = $reflection;

		// Reset the width and height
		$this->width = imagesx($reflection);
		$this->height = imagesy($reflection);

		return $success;
	}

	/**
	 * Watermark function for GD
	 *
	 * @param Image $watermark	Watermark (Image)
	 * @param int   $offset_x	Offset from the left
	 * @param int   $offset_y	Offset from thr top
	 * @param int   $opacity	Opacity of watermark
	 *
	 * @throws Image_Exception
	 *
	 * @return bool
	 */
	protected function _do_watermark(Image $watermark, $offset_x, $offset_y, $opacity): bool
	{
		// Check if function is available in GD
		if (empty(Image_GD::$_available_functions[Image_GD::IMAGELAYEREFFECT]))
		{
			throw new Image_Exception('This method requires "imagelayereffect", which is only available in the bundled version of GD'); // @codeCoverageIgnore
		}

		// Loads image if not yet loaded
		$this->_load_image();

		// Create the watermark image resource
		$overlay = imagecreatefromstring($watermark->render());

		imagesavealpha($overlay, TRUE);

		// Get the width and height of the watermark
		$width = imagesx($overlay);
		$height = imagesy($overlay);

		if ($opacity < 100)
		{
			// Convert an opacity range of 0-100 to 127-0
			$opacity = round(abs(($opacity * 127 / 100) - 127));

			// Allocate transparent gray
			$color = imagecolorallocatealpha($overlay, 127, 127, 127, $opacity);

			// The transparent image will overlay the watermark
			imagelayereffect($overlay, IMG_EFFECT_OVERLAY);

			// Fill the background with the transparent color
			imagefilledrectangle($overlay, 0, 0, $width, $height, $color);
		}

		// Alpha blending must be enabled on the background!
		imagealphablending($this->_image, TRUE);

		if ($success = imagecopy($this->_image, $overlay, $offset_x, $offset_y, 0, 0, $width, $height))
		{
			// Destroy the overlay image
			imagedestroy($overlay);
		}

		return $success;
	}

	/**
	 * Background function for GD
	 *
	 * @param int $r			Red
	 * @param int $g			Green
	 * @param int $b			Blue
	 * @param int $opacity		Opacity of background
	 *
	 * @return bool
	 */
	protected function _do_background($r, $g, $b, $opacity): bool
	{
		// Loads image if not yet loaded
		$this->_load_image();

		// Convert an opacity range of 0-100 to 127-0
		$opacity = round(abs(($opacity * 127 / 100) - 127));

		// Create a new background
		$background = $this->_create($this->width, $this->height);

		// Allocate the color
		$color = imagecolorallocatealpha($background, $r, $g, $b, $opacity);

		// Fill the image with white
		imagefilledrectangle($background, 0, 0, $this->width, $this->height, $color);

		// Alpha blending must be enabled on the background!
		imagealphablending($background, TRUE);

		// Copy the image onto a white background to remove all transparency
		if ($success = imagecopy($background, $this->_image, 0, 0, 0, 0, $this->width, $this->height))
		{
			// Swap the new image for the old one
			imagedestroy($this->_image);
			$this->_image = $background;
		}

		return $success;
	}

	/**
	 * Execute a save.
	 *
	 * @param string  $file    New image filename
	 * @param integer $quality Image quality
	 *
	 * @throws Image_Exception
	 *
	 * @return  boolean
	 */
	protected function _do_save($file, $quality): bool
	{
		// Loads image if not yet loaded
		$this->_load_image();

		// Get the extension of the file
		$extension = pathinfo($file, PATHINFO_EXTENSION);

		// Get the save function and IMAGETYPE
		[$save, $type] = $this->_save_function($extension, $quality);

		// Save the image to a file
		$status = isset($quality) ? $save($this->_image, $file, $quality) : $save($this->_image, $file);

		if ($status === TRUE AND $type !== $this->type)
		{
			// Reset the image type and mime type
			$this->type = $type;
			$this->mime = image_type_to_mime_type($type);
		}

		return $status;
	}

	/**
	 * Render function for GD
	 *
	 * @param string $type		Image type: png, jpg, gif, etc
	 * @param int    $quality	Image quality
	 *
	 * @throws Image_Exception
	 *
	 * @return string
	 */
	protected function _do_render($type, $quality): string
	{
		// Loads image if not yet loaded
		$this->_load_image();

		// Get the save function and IMAGETYPE
		[$save, $type] = $this->_save_function($type, $quality);

		// Capture the output
		ob_start();

		// Render the image
		$status = isset($quality) ? $save($this->_image, NULL, $quality) : $save($this->_image, NULL);

		if ($status === TRUE && $type !== $this->type)
		{
			// Reset the image type and mime type
			$this->type = $type;
			$this->mime = image_type_to_mime_type($type);
		}

		return ob_get_clean();
	}

	/**
	 * Get the GD saving function and image type for this extension.
	 * Also normalizes the quality setting.
	 *
	 * @param string  $extension Image type: png, jpg, etc
	 * @param integer $quality   Image quality
	 *
	 * @throws  Image_Exception
	 *
	 * @return  array
	 */
	protected function _save_function($extension, & $quality) : array
	{
		// Grep extension
		if ( ! $extension)
		{
			// Use the current image type
			$extension = image_type_to_extension($this->type, FALSE);
		}

		// Make lowercase for comparison
		$extension = strtolower($extension);

		// jpe and jpg are both valid extensions for jpeg
		if (in_array($extension, [
			'jpe',
			'jpg',
		]))
		{
			$extension = 'jpeg';
		}

		$save = 'image' . $extension;

		// Check if image (save) function exists
		// @codeCoverageIgnoreStart
		if ( ! function_exists($save))
		{
			throw new Image_Exception(
				'Installed GD does not support ":ext" images',
				[':type' => $extension]
			);
		}
		// @codeCoverageIgnoreEnd

		$type = static::extension_to_image_type($extension);

		// GIFs do not a quality setting
		if ($extension === 'gif')
		{
			$quality = NULL;
		}

		// Use a compression level of 9 (does not affect quality!)
		elseif ($extension === 'png')
		{
			$quality = 9;
		}

		return [$save, $type];
	}

	/**
	 * Create an empty image with the given width and height.
	 *
	 * @param integer $width  Image width
	 * @param integer $height Image height
	 *
	 * @return  resource
	 */
	protected function _create($width, $height)
	{
		// Create an empty image
		$image = imagecreatetruecolor($width, $height);

		// Do not apply alpha blending
		imagealphablending($image, FALSE);

		// Save alpha levels
		imagesavealpha($image, TRUE);

		return $image;
	}

	/**
	 * Return if image type is supported by GD
	 *
	 * @param int $type IMAGETYPE_* Constant
	 *
	 * @return bool
	 */
	protected function _is_supported_type(int $type): bool
	{
		// Format Extension
		$ext = strtoupper(substr(image_type_to_extension($type), 1));

		// Detect formats not following the convention
		if ($ext === 'WEBP')
		{
			$ext = 'WebP';
		}
		elseif ($ext === 'GIF')
		{
			$ext = 'GIF Create';
		}

		$ext .= ' Support';

		// Check if supported
		return gd_info()[$ext] ?? FALSE;
	}
}
