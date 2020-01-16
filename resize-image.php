<?php
// New Dimensions
$newWidth	= 500;
$newHeight	= 350;

// Min Dimensions - Keep the minimum of the new dimensions
$minDimensions = true;

// Exact Dimensions - Keep the exact new dimensions, cropping is required
$exactDimensions = true;

// Image Quality
$quality = 100;

// Image Path
$imagePath 		= __DIR__ . "/banner.jpg";
$newImagePath	= __DIR__ . "/banner-new.jpg";

// Obtain Image
$image = null;

$extension = strtolower( strrchr( $imagePath, '.' ) );

switch( $extension ) {

	// JPEG
	case '.jpg':
	case '.jpeg': {

		$image = @imagecreatefromjpeg( $imagePath );

		break;
	}
	// GIF
	case '.gif': {

		$image = @imagecreatefromgif( $imagePath );

		break;
	}
	// PNG
	case '.png': {

		$image = @imagecreatefrompng( $imagePath );

		break;
	}
}

if( empty( $image ) ) {

	die( "Provide a valid image." );
}

// Get the Image dimensions
$width	= imagesx( $image );
$height	= imagesy( $image );

// Image Aspect Ratio
$aspectRatio = $width / $height;

// Get the optimal Image dimensions
$optimalWidth	= $aspectRatio >= 1 ? $newWidth : ($aspectRatio * $newHeight);
$optimalHeight	= $aspectRatio <= 1 ? $newHeight : ($newWidth / $aspectRatio);

// Keep the minimum of the new dimensions
if( $minDimensions ) {

	$heightRatio = $height / $newHeight;
	$widthRatio	 = $width / $newWidth;

	if( $heightRatio < $widthRatio ) {

		$optimalRatio = $heightRatio;
	}
	else {

		$optimalRatio = $widthRatio;
	}

	$optimalWidth	= $width / $optimalRatio;
	$optimalHeight	= $height / $optimalRatio;
}

// Stats
echo "Width: $width Height: $height \n";
echo "Optimal Width: $optimalWidth Optimal Height: $optimalHeight \n";

// Optimal Dimensions using New Dimensions

$tempImage = imagecreatetruecolor( $optimalWidth, $optimalHeight );

if( $extension == '.png' ) {

	imagealphablending( $tempImage, false );
	imagesavealpha( $tempImage, true );

	$transparent = imagecolorallocatealpha( $tempImage, 255, 255, 255, 127 );

	imagefilledrectangle( $tempImage, 0, 0, $optimalWidth, $optimalHeight, $transparent );
}

imagecopyresampled( $tempImage, $image, 0, 0, 0, 0, $optimalWidth, $optimalHeight, $width, $height );

// Crop to get exact dimensions
if( $exactDimensions ) {

	// Crop Position
	$cropStartX	 = ( $optimalWidth / 2) - ( $newWidth / 2 );
	$cropStartY	 = ( $optimalHeight / 2) - ( $newHeight / 2 );

	// Crop the temporary image
	$crop = $tempImage;

	// Crop from center
	$tempImage = imagecreatetruecolor( $newWidth, $newHeight );

	if( $extension == '.png' ) {

		imagealphablending( $tempImage, false );
		imagesavealpha( $tempImage, true );

		$transparent = imagecolorallocatealpha( $tempImage, 255, 255, 255, 127 );

		imagefilledrectangle( $tempImage, 0, 0, $newWidth, $newWidth, $transparent );
	}

	imagecopyresampled( $tempImage, $crop, 0, 0, $cropStartX, $cropStartY, $newWidth, $newHeight, $newWidth, $newHeight );
}

// Save New Image
switch( $extension ) {

	case '.jpg':
	case '.jpeg': {

		if( imagetypes() & IMG_JPG ) {

			imagejpeg( $tempImage, $newImagePath, $quality );
		}

		break;
	}
	case '.gif': {

		if( imagetypes() & IMG_GIF ) {

			imagegif( $tempImage, $newImagePath );
		}

		break;
	}
	case '.png': {

		$scale = round( ( $quality / 100 ) * 9 );

		$invert = 9 - $scale;

		if( imagetypes() & IMG_PNG ) {

			imagepng( $tempImage, $newImagePath, $invert );
		}

		break;
	}
}

imagedestroy( $tempImage );
