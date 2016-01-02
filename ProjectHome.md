A PHP class to create a rotated polaroid image with text.

A fork of an existing polaroider-class. Added ImageMagick interoperability.


# Introduction #
This class is a fork of the http://sivel.net code: http://code.google.com/p/mattmartz/source/browse/trunk/polaroid-on-the-fly/gen-polaroid.php?r=16

# Changes to the original code #
Had problems with the imagerotate-function, so added a passthrough to ImageMagick. The end result is an anti-aliased transparent png-image.