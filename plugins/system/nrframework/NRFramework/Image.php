<?php

/**
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

namespace NRFramework;

// No direct access
defined('_JEXEC') or die;

use NRFramework\Mimes;
use NRFramework\File;
use Joomla\CMS\Image\Image as JoomlaImage;
use Joomla\Filesystem\Path;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

class Image
{
	/**
	 * Resize an image.
	 * 
	 * @param   string   $source
	 * @param   string   $width
	 * @param   string   $height
	 * @param   integer  $quality
	 * @param   string   $mode
	 * @param   boolean  $unique_filename
	 * @param   boolean  $fix_orientation
	 * @param   string 	 $gif_mode			If the uploaded image is a GIF image, how will it be copied? Options: "copy" source, "resize" source
	 * 
	 * @return  mixed
	 */
	public static function resize($source, $width, $height, $quality = 70, $mode = 'crop', $destination = '', $unique_filename = false, $fix_orientation = true, $gif_mode = 'copy')
	{
		$width = (int) $width;
		$height = (int) $height;

		// Destination file name
		$destination = empty($destination) ? $source : $destination;

		// size must be WIDTHxHEIGHT
		$size = $width . 'x' . $height;

		switch ($mode)
		{
			// Crop and Resize
			case 'crop':
				$mode = 5;
				break;
			// Scale Fill
			case 'stretch':
				$mode = 1;
				break;
			// Fit, will fill empty space with black
			case 'fit':
				$mode = 6;
				break;
			default:
				$mode = 5;
				break;
		}

		try {
			$image = new JoomlaImage($source);

			$origWidth = $image->getWidth();
			$origHeight = $image->getHeight();

			/**
			 * If the image width is less than the given width,
			 * set the image width we are resizing to the image's width.
			 */
			if ($origWidth < $width)
			{
				$size = $origWidth . 'x';

				if ($origHeight < $height)
				{
					$size .= $origHeight;
				}
				else
				{
					$size .= $height;
				}
			}
			else if ($origHeight < $height)
			{
				$prefix = $width;

				if ($origWidth < $width)
				{
					$prefix = $origWidth;
				}
				
				$size = $prefix . 'x' . $origHeight;
			}

			// Fix orientation
			if ($fix_orientation)
			{
				self::fixOrientation($image);
			}

			// Determine the MIME of the original file to get the proper type
			$mime = Mimes::detectFileType($source);
			
			// PNG images should not have a quality value
			$options = $mime == 'image/png' ? ['quality' => 9] : ['quality' => $quality];
			
			// Get the image type
			$image_type = self::getImageType($mime);

			if ($unique_filename)
			{
				// Make destination file unique
				File::uniquefy($destination);
			}

			$destination = Path::clean($destination);

			// Resize image
			if ($mime === 'image/gif')
			{
				if ($gif_mode === 'copy')
				{
					File::copy($source, $destination, true);
				}
				else
				{
					foreach ($image->generateThumbs($size, $mode) as $thumb)
					{
						$thumb->toFile($destination, $image_type, $options);
					}
				}
			}
			else
			{
				foreach ($image->generateThumbs($size, $mode) as $thumb)
				{
					$thumb->toFile($destination, $image_type, $options);
				}
			}

			return $destination;
		} catch(\Exception $e) {}

		return false;
	}

	/**
	 * Resizes an image by height.
	 * 
	 * @param   string  $src
	 * @param   string  $height
	 * @param   string  $destination
	 * @param   int	 	$quality
	 * @param   bool 	$unique_filename
	 * @param   bool 	$fix_orientation
	 * @param   string 	$gif_mode			If the uploaded image is a GIF image, how will it be copied? Options: "copy" source, "resize" source
	 * 
	 * @return  bool
	 */
	public static function resizeByHeight($src, $height, $destination = null, $quality = 70, $unique_filename = false, $fix_orientation = true, $gif_mode = 'copy')
	{
		$height = (int) $height;
		
		// Create a new JImage object from the source image path
		$image = new JoomlaImage($src);

		// Fix orientation
		if ($fix_orientation)
		{
			self::fixOrientation($image);
		}
	  
		// Determine the MIME of the original file to get the proper type
		$mime = Mimes::detectFileType($src);

		// Get the image type
		$image_type = self::getImageType($mime);

		// Output file name
		$destination = empty($destination) ? $src : $destination;
		
		if ($unique_filename)
		{
			// Make destination file unique
			File::uniquefy($destination);
		}

		$destination = Path::clean($destination);
		
		// PNG images should not have a quality value
		$options = $mime == 'image/png' ? ['quality' => 9] : ['quality' => $quality];

		// Get the original width and height of the image
		$origWidth = $image->getWidth();
		$origHeight = $image->getHeight();
	  
		// Calculate the new width based on the desired height
		$newWidth = ($origWidth / $origHeight) * $height;

		// Resize image
		if ($mime === 'image/gif')
		{
			if ($gif_mode === 'copy')
			{
				File::copy($source, $destination, true);
			}
			else
			{
				$resizedImage = $image->resize($newWidth, $height);
				$resizedImage->toFile($destination, $image_type, $options);
			}
		}
		else
		{
			$resizedImage = $image->resize($newWidth, $height);
			$resizedImage->toFile($destination, $image_type, $options);
		}
	  
		// Return true if the image was successfully resized and saved, false otherwise
		return $destination;
	}

	/**
	 * Resizes an image by keeping the aspect ratio
	 * 
	 * @param   string   $source
	 * @param   array    $width
	 * @param   integer  $quality
	 * @param   array    $destination
	 * @param   boolean  $unique_filename
	 * @param   boolean  $fix_orientation
	 * @param   string 	 $gif_mode			If the uploaded image is a GIF image, how will it be copied? Options: "copy" source, "resize" source
	 * 
	 * @return  boolean
	 */
	public static function resizeAndKeepAspectRatio($source, $width, $quality = 70, $destination = '', $unique_filename = false, $fix_orientation = true, $gif_mode = 'copy')
	{
		// Ensure we have received valid image dimensions
		if (!count($image_dimensions = getimagesize($source)))
		{
			return false;
		}

		// Get the image width
		if (!$uploaded_image_width = (int) $image_dimensions[0])
		{
			return false;
		}

		// Get the image height
		if (!$uploaded_image_height = (int) $image_dimensions[1])
		{
			return false;
		}

		$width = (int) $width;

		/**
		 * If the image width is less than the given width,
		 * set the image width we are resizing to the image's width.
		 */
		if ($uploaded_image_width < (int) $width)
		{
			$width = $uploaded_image_width;
		}

		// Determine the MIME of the original file to get the proper type
		$mime = Mimes::detectFileType($source);

		// PNG images should not have a quality value
		$options = $mime == 'image/png' ? ['quality' => 9] : ['quality' => $quality];

		// Get the image type
		$image_type = self::getImageType($mime);

		try {
			// Get image object
			$image = new JoomlaImage($source);

			// Fix orientation
			if ($fix_orientation)
			{
				self::fixOrientation($image);
			}

			// Calculate aspect ratio
			$ratio = $uploaded_image_width / $uploaded_image_height;
	
			// Get new height based on aspect ratio
			$targetHeight = $width / $ratio;
	
			// Output file name
			$destination = empty($destination) ? $source : $destination;

			if ($unique_filename)
			{
				// Make destination file unique
				File::uniquefy($destination);
			}
	
			$destination = Path::clean($destination);
			
			// Resize image
			if ($mime === 'image/gif')
			{
				if ($gif_mode === 'copy')
				{
					File::copy($source, $destination, true);
				}
				else
				{
					$resizedImage = $image->resize($width, $targetHeight, true);
					$resizedImage->toFile($destination, $image_type, $options);
				}
			}
			else
			{
				$resizedImage = $image->resize($width, $targetHeight, true);
				$resizedImage->toFile($destination, $image_type, $options);
			}

			return $destination;
		} catch(\Exception $e) {}

		return false;
	}

	public static function resizeByWidthOrHeight($source, $width, $height, $quality = 80, $destination = '', $resize_method = 'crop', $unique_filename = false, $fix_orientation = true)
	{
		$resized_image = null;
		
		// If width is null, and we have height set, we are resizing by height
		if (is_null($width) && $height && !is_null($height))
		{
			$resized_image = Image::resizeByHeight($source, $height, $destination, 80, $unique_filename, $fix_orientation);
		}
		else
		{
			/**
			 * If height is zero, then we suppose we want to keep aspect ratio.
			 * 
			 * Resize with width & height: If height is not set
			 * Resize and keep aspect ratio: If height is set
			 */
			$resized_image = $height && !is_null($height)
				?
				Image::resize($source, $width, $height, 80, $resize_method, $destination, $unique_filename, $fix_orientation)
				:
				Image::resizeAndKeepAspectRatio($source, $width, 80, $destination, $unique_filename, $fix_orientation);

		}

		return $resized_image;
	}

	/**
	 * Returns the orientation of the image.
	 * 
	 * @param   string  $path
	 * 
	 * @return  int
	 */
	public static function getOrientation($path)
	{
		if (!$exif = @exif_read_data($path))
		{
			return;
		}

		return intval(@$exif['Orientation']);
	}

	/**
	 * Fixes the orientation of the generated image and ensures it appears with the same orientation as the source.
	 * 
	 * @param    string  $path
	 * @param    int     $orientation
	 * 
	 * @return   void
	 */
	public static function fixOrientation(&$image, $orientation = null)
	{
		$orientation = self::getOrientation($image->getPath());
		
		if(!in_array($orientation, [3, 6, 8]))
		{
			return;
		}

		switch ($orientation)
		{
            case 3:
                $image->rotate(180, -1, false);
                break;

            case 6:
                $image->rotate(270, -1, false);
                break;

            case 8:
                $image->rotate(90, -1, false);
                break;
		}

		return true;
	}

	/**
	 * Returns the image type based on its mime type
	 * 
	 * @param   string  $mime
	 * 
	 * @return  int
	 */
	public static function getImageType($mime)
	{
		switch ($mime)
		{
			case 'image/png':
				return IMAGETYPE_PNG;
				break;
			case 'image/gif':
				return IMAGETYPE_GIF;
				break;
			case 'image/jpeg':
			default:
				return IMAGETYPE_JPEG;
				break;
		}
	}

	/**
	 * Creates a watermark from text.
	 * 
	 * @param   string   $text
	 * @param   integer  $font_size
	 * @param   integer  $opacity
	 * @param   string   $color
	 * @param   integer  $originalWidth
	 * @param   integer  $originalHeight
	 * 
	 * @return  object
	 */
	public static function createWatermarkText($text = '', $_font_size = 30, $opacity = 60, $color = '#ffffff', $originalWidth = null, $originalHeight = null)
	{
		$font = implode(DIRECTORY_SEPARATOR, [JPATH_SITE, 'media', 'plg_system_nrframework', 'font', 'arial.ttf']);

		// Scale down font size based on the original image width or height
		$dimension = $originalWidth > $originalHeight ? $originalWidth : $originalHeight;
		$font_size = $dimension ? $_font_size * ($dimension / 1000) * 1.2 : $_font_size;

		if ($font_size > $_font_size)
		{
			$font_size = $_font_size;
		}

		if ($font_size < 14)
		{
			$font_size = 14;
		}

		$TextSize = @ImageTTFBBox($font_size, 0, $font, $text) or die;
		$TextWidth = abs($TextSize[2]) + abs($TextSize[0]);
		$TextHeight = abs($TextSize[7]) + abs($TextSize[1]);

		$watermarkImage = imagecreatetruecolor($TextWidth, $TextHeight);

		imagealphablending($watermarkImage, false);
		imagesavealpha($watermarkImage, true);
		$bgText = imagecolorallocatealpha($watermarkImage, 255, 255, 255, 127);
		imagefill($watermarkImage, 0, 0, $bgText);
		$wmTransp = 127 - ($opacity * 1.27);
		$rgb = self::hex2rgb($color, false);
		$colorResource = imagecolorallocatealpha($watermarkImage, $rgb[0], $rgb[1], $rgb[2], $wmTransp);
		
		// Create watermark
		imagettftext($watermarkImage, $font_size, 0, 0, abs($TextSize[5]), $colorResource, $font, $text);

		return $watermarkImage;
	}

	/**
	 * Apply the watermark.
	 * 
	 * @param   array  $opts
	 * 
	 * @return  void
	 */
	public static function applyWatermark($opts = [])
	{
		$defaults = [
			'source' => null,
			'destination' => null,
			'preset' => 'custom',
			'type' => 'text',
			'text' => null,
			'position' => 'bottom-right',
			'angle' => 0,
			'opacity' => 50,
			'size' => 30,
			'color' => '#fff'
		];

		$opts = array_merge($defaults, $opts);

		if (!$opts['source'])
		{
			return false;
		}

		$destination = $opts['destination'] ? $opts['destination'] : $opts['source'];

		$originalImage = new JoomlaImage($opts['source']);

		// Get the dimensions of the original image
		$originalWidth = $originalImage->getWidth();
		$originalHeight = $originalImage->getHeight();

		$watermarkSource = null;

		switch ($opts['type'])
		{
			case 'image':
				$watermarkSource = $opts['image'];
				break;
			
			case 'text':
			default:
				if (!$watermarkText = self::getWatermarkText($opts['text_preset'], $opts['text'], $destination))
				{
					return;
				}

				$watermarkSource = self::createWatermarkText($watermarkText, (int) $opts['size'], 100, $opts['color'], $originalWidth, $originalHeight);
				break;
		}

		if (!$watermarkSource)
		{
			return;
		}

		$original_image_mime = $originalImage->getImageFileProperties($opts['source'])->mime;

		// Create the final image
		$finalImage = imagecreatetruecolor($originalWidth, $originalHeight);

		$watermarkOpacity = (int) $opts['opacity'];

		// Add a black background color if the image is PNG and watermark opacity is not 100, as imagecopymerge() doesn't work with transparent PNG images
		if ($original_image_mime === 'image/png')
		{
			if ($watermarkOpacity === 100)
			{
				imagesavealpha($finalImage, true);
				$trans_background = imagecolorallocatealpha($finalImage, 0, 0, 0, 127);
				imagefill($finalImage, 0, 0, $trans_background);
			}
			else
			{
				$black = imagecolorallocate($finalImage, 0, 0, 0);
				imagefill($finalImage, 0, 0, $black);
			}
		}

		// Copy original image to the final image
		imagecopy($finalImage, $originalImage->getHandle(), 0, 0, 0, 0, $originalWidth, $originalHeight);

		// Get watermark image
		$watermarkImage = new JoomlaImage($watermarkSource);

		// Rotate it
		if ($opts['angle'])
		{
			$angle = $opts['angle'] ? 360 - (int) $opts['angle'] : 0;
			$watermarkImage->rotate($angle, -1, false);
		}

		// Get the dimensions of the watermark image
		$watermarkWidth = $watermarkImage->getWidth();
		$watermarkHeight = $watermarkImage->getHeight();

		if (!$watermarkWidth || !$watermarkHeight)
		{
			return false;
		}

		// Final watermark width/height
		$width = $watermarkWidth;
		$height = $watermarkHeight;

		// Scale watermark image
		if ($opts['type'] === 'image')
		{
			$scaleFactor = min($originalWidth / $watermarkWidth, $originalHeight / $watermarkHeight);

			// Calculate the new dimensions of the watermark image
			$width = $watermarkWidth * $scaleFactor;
			$height = $watermarkHeight * $scaleFactor;

			if ($width > $watermarkWidth)
			{
				$width = $watermarkWidth;
				$height = $watermarkHeight;
			}
		}
		
		$width = (int) $width;
		$height = (int) $height;

		list($dest_x, $dest_y) = self::getWatermarkPosition($opts['position'], $originalWidth, $originalHeight, $width, $height);

		// Resize watermark image before applying it into the final image
		$watermarkImage = $watermarkImage->resize($width, $height);

		// Copy the watermark image into the final image
		if ($watermarkOpacity === 100)
		{
			imagecopy($finalImage, $watermarkImage->getHandle(), round($dest_x), round($dest_y), 0, 0, $width, $height);
		}
		else
		{
			self::imagecopymerge_alpha($finalImage, $watermarkImage->getHandle(), round($dest_x), round($dest_y), 0, 0, $width, $height, $watermarkOpacity);
		}

		// Save final image
		switch ($original_image_mime)
		{
			case 'image/gif':
				imagegif($finalImage, $destination);
				break;
			
			case 'image/webp':
				imagewebp($finalImage, $destination, 70);
				break;

			case 'image/png':
				imagepng($finalImage, $destination, 6);
				break;

			default:
				imagejpeg($finalImage, $destination, 70);
				break;
		}

		imagedestroy($finalImage);
		$originalImage->destroy();
		$watermarkImage->destroy();
	}

	/**
	 * imagecopy but with alpha channel support.
	 * 
	 * @param   object   $dst_im
	 * @param   object   $src_im
	 * @param   integer  $dst_x
	 * @param   integer  $dst_y
	 * @param   integer  $src_x
	 * @param   integer  $src_y
	 * @param   integer  $src_w
	 * 
	 * @return  void
	 */
	public static function imagecopymerge_alpha($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct)
	{
		$cut = imagecreatetruecolor($src_w, $src_h);
		
        // copying relevant section from background to the cut resource
        imagecopy($cut, $dst_im, 0, 0, $dst_x, $dst_y, $src_w, $src_h);
		
        // copying relevant section from watermark to the cut resource
        imagecopy($cut, $src_im, 0, 0, $src_x, $src_y, $src_w, $src_h);

        // insert cut resource to destination image
        imagecopymerge($dst_im, $cut, $dst_x, $dst_y, 0, 0, $src_w, $src_h, $pct);
    }

	/**
	 * Returns the watermark position.
	 * 
	 * @param   string   $position
	 * @param   integer  $originalWidth
	 * @param   integer  $originalHeight
	 * @param   integer  $width
	 * @param   integer  $height
	 * 
	 * @return  array
	 */
	public static function getWatermarkPosition($position, $originalWidth, $originalHeight, $width, $height)
	{
		/**
		 * Position watermark based on given position.
		 * 
		 * top-left
		 * top-center
		 * top-right
		 * center-left
		 * center-center
		 * center-right
		 * bottom-left
		 * bottom-center
		 * bottom-right
		 */
		$position = explode('-', $position);

		// Padding from corner
		$yPOS = $xPOS = 10;
		$dest_x = $dest_y = 0;
		
		if (isset($position[0]))
		{
			switch ($position[0])
			{
				case 'top':
					$dest_y = 0 + $yPOS;
					break;
				case 'center':
					$dest_y = round($originalHeight / 2) - round($height / 2);
					break;
				case 'bottom':
					$dest_y = $originalHeight - $height - $yPOS;
					break;
			}
		}

		if (isset($position[1]))
		{
			switch ($position[1])
			{
				case 'left':
					$dest_x = 0 + $xPOS;
					break;
				case 'center':
					$dest_x = round($originalWidth / 2) - round($width / 2);
					break;
				case 'right':
					$dest_x = $originalWidth - $width - $xPOS;
					break;
			}
		}

		return [$dest_x, $dest_y];
	}

	/**
	 * Returns the watermark text.
	 * 
	 * @param   string  $preset
	 * @param   string  $text
	 * @param   string  $filename
	 * 
	 * @return  string
	 */
	public static function getWatermarkText($preset = '', $text = '', $filename = '')
	{
		switch ($preset)
		{
			case 'site_name':
				$text = Factory::getApplication()->get('sitename');
				break;
			
			case 'site_url':
				$text = Uri::root();
				break;

			case 'custom':
				$st = new \NRFramework\SmartTags();

				// Add file Smart Tags
				$file_data = File::pathinfo($filename);
				$source_basename = $file_data['basename'];
				$file_data['filename'] = $file_data['filename'];
				$file_data['basename'] = $source_basename;

				$st->add($file_data, 'file.');

				
				$text = $st->replace($text);
				break;
		}

		return $text;
	}

	/**
	 * Converts hexidecimal color value to rgb values and returns as array/string
	 *
	 * @param   string 		  $hex
	 * @param   bool		  $asString
	 * 
	 * @return  array|string
	 */
	public static function hex2rgb($hex, $asString = false)
	{
        // strip off any leading #
        if (0 === strpos($hex, '#'))
		{
           $hex = substr($hex, 1);
        }
		else if (0 === strpos($hex, '&H'))
		{
           $hex = substr($hex, 2);
        }

        // break into hex 3-tuple
        $cutpoint = ceil(strlen($hex) / 2)-1;
        $rgb = explode(':', wordwrap($hex, $cutpoint, ':', $cutpoint), 3);

        // convert each tuple to decimal
        $rgb[0] = (isset($rgb[0]) ? hexdec($rgb[0]) : 0);
        $rgb[1] = (isset($rgb[1]) ? hexdec($rgb[1]) : 0);
        $rgb[2] = (isset($rgb[2]) ? hexdec($rgb[2]) : 0);

        return ($asString ? "{$rgb[0]} {$rgb[1]} {$rgb[2]}" : $rgb);
    }
}