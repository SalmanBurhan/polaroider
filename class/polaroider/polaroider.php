<?php
//include_once("altimagerotate.php");

/*
Copyright (C) 2004-2007 The Polaroid-o-nizer Team

Copyright (c) 2007 Matt Martz (http://sivel.net)

Modified/forked in 2010-2011 by Lasse Korpela and Manuel Bacso 
from Aalto University / SimLab

This file was intended for distribution with the Polaroid
on the Fly Wordpress plugin by Matt Martz.  See
polaroid-on-the-fly.php for addition information.

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
*/


/*
CLASS USAGE:
Polaroid(photoURL, background color "rrr,ggg,bbb", roation angle (-15 to +15 degrees), text to be added, xoffset, yoffset)

*/
class Polaroid
{
	var $class_dir;
	
	var $errmsg = array();
	var $photo;
	var $text;
	var $x;
	var $y;
	
	var $imagedata;

	function Polaroid($photourl, $bg, $angle, $text, $x, $y)
	{
		//Should work on all platforms
		$this->class_dir =  dirname ( __FILE__ ) . "/";
		
		$this->photo = $photourl;
		$this->text = $text;
		$this->angle = $angle;
		$this->bg = $bg;
		$this->x = $x;	
		$this->y = $y;	
		
		if (empty($this->bg))
		{	
			$this->bg = '255,255,255';
		}
		else
		{
			$bg = explode(",", $this->bg);
			for ($i = 0; $i < 3; $i++)
			{
				if ($bg[$i] < 0 || $bg[$i] > 255 || !is_numeric($bg[$i]))
				{
					$this->bg = '255,255,255';
					$this->photo = $this->class_dir . 'bg.jpg'; //backgronud color error
				}
			}
		}

		$this->x = !empty($this->x) ? $this->x : 0;
		$this->y = !empty($this->y) ? $this->y : 0;

		if ((isset($this->x) && !is_numeric($this->x)) || (isset($this->y) && !is_numeric($this->y)))
		{
			$this->errmsg['xy'] = "Incorrect x and/or y coordinates:";
		}

		$this->angle = !empty($this->angle) || $this->angle == "0" ? $this->angle : 15;

		if ($this->angle < 0 || $this->angle > 360 || !is_numeric($this->angle))
		{
			$this->errmsg['angle'] = "Incorrect rotation angle:";
		}

		$this->photo = str_replace(" ", "%20", $this->photo);
		$info = @getimagesize($this->photo);
		if(!$info)
		{
			$this->photo = $this->class_dir . 'url.jpg';
		}
		elseif(!in_array($info[2], array(1, 2, 3)))
		{
			$this->photo = $this->class_dir . 'filetype.jpg';
		}
		elseif ($info[0] >= 2000 || $info[1] >= 2000)
		{
			$this->photo = $this->class_dir . 'maxres.jpg';
		}
	}

	function CreatePolaroid()
	{
		
		$text = trim(strip_tags(stripslashes(str_replace("_", " ", $this->text))));
		
		//Create background for the image frop a transparent polaroid background
		$polaroid = imagecreatefrompng($this->class_dir . "frame-white.png");
		imagealphablending($polaroid, true); // setting alpha blending on
		imagesavealpha($polaroid, true); // save alphablending setting (important)

		//Photo URL to add to the polaroid background
		$photo = str_replace(" ", "%20", $this->photo);
		$info = getimagesize($photo);

		$scale = round(($info[0] > $info[1]) ? (200 / $info[1]) : (200 / $info[0]), 4);
		
		// Get new dimensions
		list($width_orig, $height_orig) = getimagesize($photo);

		
		//Create image based on the filetype
		if ($info[2] == 1)
		{
			$photo = imagecreatefromgif(stripslashes($photo));
		}
		elseif ($info[2] == 2)
		{
			$photo = imagecreatefromjpeg(stripslashes($photo));
		}
		elseif ($info[2] == 3)
		{
			$photo = imagecreatefrompng(stripslashes($photo));
		}

		imagealphablending($photo, true); // setting alpha blending on
		imagesavealpha($photo, true); // save alphablending setting (important)
		
		// Set a maximum height and width
		$dwidth = 200;
		$dheight = 200;
		$newwidth = $dwidth;
		$newheight = $dheight;
		
		// Get new dimensions
		$ratio_orig = $width_orig/$height_orig;

		if ($dwidth/$dheight > $ratio_orig) {
			$newwidth = $dheight*$ratio_orig;
		} else {
			$newheight = $dwidth/$ratio_orig;
		}

		// Resample
		$image_p = imagecreatetruecolor($newwidth, $newheight);
		
		//calculate new x&y offsets so the picture is centered
		$xoffset = 20 + floor(($dwidth - $newwidth) / 2);
		$yoffset = 18;
		
		//Copy a resized version of the original image to polaroid background
		imagecopyresampled($polaroid, $photo, $xoffset, $yoffset, 0, 0, $newwidth, $newheight, $width_orig, $height_orig);
		

		//Wrap text to multiple lines
		$text = wordwrap($text, 25, "||", 1);
		$textarray = explode("||", $text);
		
		//Ugly hack to get rid of notices.....
		
		if (sizeof($textarray) == 0)
			$textarray[0] = " ";
			
		if (sizeof($textarray) == 1)
			$textarray[1] = " ";
		
		if (sizeof($textarray) == 2)
			$textarray[2] = " ";
		
		$black = imagecolorallocate($polaroid, 0, 0, 0);

		//y-positions of the text in the polaroid picture
		$text_pos_y = array(225, 242, 259);
		
		//Font definitions
		$font_size = 10;
		$font_name = $this->class_dir . "fonts/acmesai.ttf";
		
		//NOTE: Only two lines of text used
		for ($i = 0; $i < 3; $i++)
		{
			$width = imagettfbbox($font_size, 0, $font_name, $textarray[$i]);
			$text_pos_x = (240- $width[2])/2; //calculate the center of the polaroid frame
			imagettftext($polaroid, $font_size, 0, $text_pos_x, $text_pos_y[$i]+10, $black, $font_name, $textarray[$i]);
		}
		
		
		
		
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') //This is a server using Windows!
			$tmpfile="c:/temp/polaroiderpic". rand(500000,10000000) . ".png";
		else                                            //This is a server not using Windows! (Maybe linux)
			$tmpfile="/tmp/polaroiderpic-". rand(500000,10000000) . ".png";
		
		//create PNG file from the polaroid image
		imagepng($polaroid , $tmpfile, 2, NULL); 
        
		
		//Send PNG header
		header("Content-type: image/png");
		//Send the tmp-image to imagemagick and pass through the result to client
        passthru("convert -background transparent -rotate $this->angle $tmpfile png:-");
		
        //remove the tmp image
        unlink($tmpfile);
		//free memory
		imagedestroy($polaroid);
		exit;
	}
}
?>