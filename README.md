# phpFileHandler

All in one PHP file handling class  
- Including image handling!

(Started 13.08.2017, not yet finished with the initial build)

## File sanity

Image files are NOT checked with the use of exif_imagetype.  
Extensions are guessed with their file signature!  
Following file types can be detected:

**_jpg (jpeg), gif, png, bmp,  
mp4, webm, webp, gzip, 7zip,  
rar, exe, tif, tiff, pdf, wav,  
avi, xml, mp3, wmv, wma_**

If the filetype could not be recognized, the mime string or at last the original name extension will be taken.  
You can enable strict file type checking, so the added files must meet one of the upper file signatures to pass.

```php
$phpFileHandler->setStrictFilecheck( true );
```

## Usage (minimum)

```php
<?php
require 'phpFileHandler.php';

$phpFileHandler = new phpFileHandler;  // create phpFileHandler object
$phpFileHandler->add_uploaded_files();  // Add all uploaded files from the $_FILES superglobal 
$phpFileHandler->save( '/Path/To/Save/' ); // Save all valid files with a new unique name (12 characters long) to the given location 

```

## Documentation

### Configuration

- **_setMaxFileSize()_**  
	Set a file size limit (in megabyte format), if not set there is no limit (note to adjust 'upload_max_filesize' & 'post_max_size' in php.ini)  
	If **$isMB** set to false, **$size** can be passed in byte format  
	
- **_setAllowedFileTypes()_**	// default: false  
	Set allowed file types, if not set (or false is passed) all filetypes are allowed  
	Example for 'jpg' and 'png' only: **->setAllowedFileTypes( ['jpg','png'] );**  
	Presets can be used:  
    - **phpFileHandler::FTY_IMAGES_COMMON** // ['jpg','gif','png']  
    - **phpFileHandler::FTY_IMAGES_GD** // ['jpg','gif','png','gd','gd2','bmp','wbmp','webp','xbm','xpm']  
    - **phpFileHandler::FTY_VIDEO_COMMON** // ['mp4','webm']  
    - **phpFileHandler::FTY_MEDIA_COMMON** // ['jpg','gif','png','mp4','webm']  
	
- **_setStrictFilecheck()_**	// default: false  
	If set to true only files can pass which signature can be identified by **phpFileHandler::guess_fileextension()**  
	This option does NOT negate **phpFileHandler::setAllowedFileTypes()**  
	Note: Files added from **phpFileHandler::add_existing_files()** are NOT affected  
	
- **_setUniqFilenames()_**	// default: true  
	If set to true, the files which are saved via **phpFileHandler::save()** will become a unique filename
	generated with **phpFileHandler::uniqString()**  
	**phpFileHandler::save()** is NOT affected from this option WHEN: called with **$file_index** AND **$name**  

- **_setUniqFilenameLength()_** // default: 12  
	Set the default filename length generated by **phpFileHandler::uniqString()** when called from **phpFileHandler::save()** 
  Note: **$length** should be atleast set to **10** for multiple files in the same folder!

```php
phpFileHandler::setMaxFileSize( int $size [, (bool) $isMB = true ] )
phpFileHandler::setAllowedFileTypes( array $types )
phpFileHandler::setStrictFilecheck( (bool) $bool )
phpFileHandler::setUniqFilenames( (bool) $bool )
phpFileHandler::setUniqFilenameLength( (integer) $length = 12 )
```

### Adding & Save files

Files can only be added via following functions.  
The adding process add an file data element for each file into:  
	'->	**phpFileHandler::Files_valid**		// files which have passed all sanity checks  
		'-> **phpFileHandler::Files_valid_count**  
	'-> **phpFileHandler::Files_invalid**	// files which have failed at least one sanity check  
		'-> **phpFileHandler::Files_invalid_count**  
	'-> **phpFileHandler::Files**					// All added files ( valid & invalid )  
		'-> **phpFileHandler::Files_count**  

A file data element looks like this:  
**array(**  
  **"path" =>** 'C:/Apache24/htdocs/data/temp/8dsfsa98df6.jpg', // the full filepath  
  **"origname" =>** 'Origin$alF &il eName.jpg', // original filename, for files from an web url this is the web url  
  **"name" =>** 'OriginalFileName',	// for uploaded files name will be a **stripped** version of "origname", for files from a web url "name" will be the last url part ('https://flushmodules.com/css/visuals/fm-icons.png' -> 'fm-icons.png' )
  **"savename" =>** 'OriginalFileName.jpg',  
  **"isnew" =>** true, // only false when file was added via phpFileHandler::add_existing_files()  
  **"size" =>** 600000, // in bytes  
  **"error" =>** '', // the error message for invalid files  
  **"isvalid" =>** true,  
  **"ext" =>** 'jpg'  
**);**

- **_add_uploaded_files()_**  
	Adds all files found in the superglobal $_FILES (upload via html form or ajax)  
	
- **_add_file_from_url()_**  
	Add a file from a web url  

- **_add_existing_files()_**  
	Add files which already are on the server  
	Single file:		**->add_existing_files( 'Path/To/My/File.jpg' );**  
	Multiple files:	**->add_existing_files( array( 'Path/To/My/File.jpg', 'Path/To/Another/File.png' ) );**  
  
- **_save()_**  
	Saves all valid files (_which has not yet been saved!_) from **phpFileHandler::Files_valid** to the specified folder (**$to**)  
  By default phpFileHandler will NOT create that folder for you, nevertheless you can pass **true** as second parameter to allow folder creation.  
  You can also save single files from **phpFileHandler::Files_valid** by passing its *array index* as third parameter.  
  It will return:  
    - **true** on success 
    - **false** on failure 
    - **null** when passing an undefined *array index* 
  As fourth parameter you can choose a filename (_only when saving with **$file_index** set!_)
  

```php
phpFileHandler::add_uploaded_files()
phpFileHandler::add_file_from_url( (string) $url )
phpFileHandler::add_existing_files( (mixed) $filenames )

phpFileHandler::save( (string) $to [, (bool) $allow_dir_create = false [, (int) $file_index = null [, (string) $name = null ]]] )
```

### Image Handling / Manipulation

Supported image file types for image Handling are:  
**_jpg (jpeg), gif, png, bmp, wbmp,  
gd, gd2, webp, xbm_**

Note: *bmp* is just supported in PHP 7 >= 7.2.0  
      *webp* is just supported in PHP 5 >= 5.5.0, PHP 7

- **_thumb()_**  
	Creates a thumbnail for every image file in **phpFileHandler::Files_valid**  
  

```php
phpFileHandler::thumb( (int) $size [, (string) $filename = null [, (string) $type = '' [, (string) $prefix = '_thumb' ]]] )
phpFileHandler::resize( (string) $filename, (int) $size [, (string) $to = null [, (string) $prefix = '' ]] )
phpFileHandler::convert( (string) $filename, (string) $output_type [, (bool) $keepOriginal = false ] )
phpFileHandler::fix_orientation( (string) $filename [, (string) $bytestream = null ] )
phpFileHandler::put_watermark( (string) $target, (string) $watermark [, (float) $opacity = 0.5 [, (string) $position = 'center' [, (int) $offsetX = 0 [, (int) $offsetY = 0 ]]]] )
```

### static utility functions

```php
phpFileHandler::uniqString( (int) $length = 12 )
phpFileHandler::move_file( (string) $filename, (string) $to [, (bool) $allow_dir_create = false [, (bool) $copy = false [, (bool) $allow_override = false ]]] )
phpFileHandler::guess_fileextension( [ (string) $filename = null [, (string) $bytestream = null ]] )
phpFileHandler::is_jpg( [ (string) $filename = null [, (array) $hexArr = null ]] )
phpFileHandler::is_gif( [ (string) $filename = null [, (array) $hexArr = null ]] )
phpFileHandler::is_png( [ (string) $filename = null [, (array) $hexArr = null ]] )
phpFileHandler::is_bmp( [ (string) $filename = null [, (array) $hexArr = null ]] )
phpFileHandler::is_webp( [ (string) $filename = null [, (array) $hexArr = null ]] )
phpFileHandler::is_webm( [ (string) $filename = null [, (array) $hexArr = null ]] )
phpFileHandler::is_gzip( [ (string) $filename = null [, (array) $hexArr = null ]] )
phpFileHandler::is_7zip( [ (string) $filename = null [, (array) $hexArr = null ]] )
phpFileHandler::is_rar( [ (string) $filename = null [, (array) $hexArr = null ]] )
phpFileHandler::is_tiff( [ (string) $filename = null [, (array) $hexArr = null ]] )
phpFileHandler::is_pdf( [ (string) $filename = null [, (array) $hexArr = null ]] )
phpFileHandler::is_wav( [ (string) $filename = null [, (array) $hexArr = null ]] )
phpFileHandler::is_avi( [ (string) $filename = null [, (array) $hexArr = null ]] )
phpFileHandler::is_tar( [ (string) $filename = null [, (array) $hexArr = null ]] )
phpFileHandler::is_xml( [ (string) $filename = null [, (array) $hexArr = null ]] )
phpFileHandler::is_mp3( [ (string) $filename = null [, (array) $hexArr = null ]] )
phpFileHandler::is_wmv( [ (string) $filename = null [, (array) $hexArr = null ]] )
phpFileHandler::is_wma( [ (string) $filename = null [, (array) $hexArr = null ]] )
```
