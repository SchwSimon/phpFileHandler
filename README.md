# phpFileHandler

All in one PHP file handling class  

## Class Features

- Handles complete file upload, sanitizing & saving process  
- Safe file handling, wherever the possibility exists to overwrite another file the default behaviour is not todo so  
- Safe folder deletion & emptying
- Include the most common image handling features like thumb creation, resizing etc.
- Automatically adjusts the image oritentation when adding image files

## Dependencies

To use the image handling functions, 
the PHP gd2 extension which PHP ships within its default package has to be loaded. 
You can check if its loaded with:
```php
$phpFileHandler->is_gd2_ext;
```


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

$phpFileHandler->thumb( 150, 'iso', '-150x150' ); // Optional thumb creation for images

```

## Documentation

### Configuration

- **_setMaxFileSize()_**  
> Set a file size limit (in megabyte format), if not set there is no limit (note to adjust 'upload_max_filesize' & 'post_max_size' in php.ini)  
> If **$isMB** set to false, **$size** can be passed in byte format
```php
$phpFileHandler->setMaxFileSize( int $size [, bool $isMB = true ] );
```
	
- **_setAllowedFileTypes()_**	// default: false  
> Set allowed file types, if not set (or false is passed) all filetypes are allowed  
> Example for 'jpg' and 'png' only: **$phpFileHandler->setAllowedFileTypes( ['jpg','png'] );**  
> Presets can be used:  
    - **phpFileHandler::FTY_IMAGES_COMMON** // ['jpg','gif','png']  
    - **phpFileHandler::FTY_IMAGES_GD** // ['jpg','gif','png','gd','gd2','bmp','wbmp','webp','xbm','xpm']  
    - **phpFileHandler::FTY_VIDEO_COMMON** // ['mp4','webm']  
    - **phpFileHandler::FTY_MEDIA_COMMON** // ['jpg','gif','png','mp4','webm']  
```php
$phpFileHandler->setAllowedFileTypes( array $types )
```

- **_setStrictFilecheck()_**	// default: false  
> If set to true only files can pass which signature can be identified by **phpFileHandler::guess_fileextension()**  
> This option does NOT negate **$phpFileHandler->setAllowedFileTypes()**  
> Note: Files added from **$phpFileHandler->add_existing_files()** are NOT affected  
```php
$phpFileHandler->setStrictFilecheck( bool $bool )
```

- **_setUniqFilenames()_**	// default: true  
> If set to true, the files which are saved via **$phpFileHandler->save()** will become a unique filename
> generated with **phpFileHandler::uniqString()**  
> **$phpFileHandler->save()** is NOT affected from this option WHEN: called with **$file_index** AND **$name**  
```php
$phpFileHandler->setUniqFilenames( bool $bool )
```

- **_setUniqFilenameLength()_** // default: 12  
> Set the default filename length generated by **phpFileHandler::uniqString()** when called from **$phpFileHandler->save()** 
> Note: **$length** should be atleast set to **10** for multiple files in the same folder!
```php
$phpFileHandler->setUniqFilenameLength( int $length = 12 )
```

### Adding & Save files

Files can only be added via following functions.  
The adding process add an file data element for each file into:  
- **$phpFileHandler->Files_valid**	// files which have passed all sanity checks  
- **$phpFileHandler->Files_valid_count**  
- **$phpFileHandler->Files_invalid**	// files which have failed at least one sanity check  
- **$phpFileHandler->Files_invalid_count**  
- **$phpFileHandler->Files_ready**	// files which are ready for further usage
- **$phpFileHandler->Files_ready_count**  

You can easy iterate over the files
```php
for( $i = 0; $i < $phpFileHandler->Files_ready_count; $i++ ) {
	// $phpFileHandler->Files_ready[$i]
}
```

A file data element key list example (from a valid & saved file):
> Note that for invalid files not all of these are set!
```php
array(
  'path' => 'C:/Apache24/htdocs/data/temp/8dsfsa98df6.jpg', // the path to the file
  'dirname' => 'C:/Apache24/htdocs/data/temp'               // the path to the file's directory
  'origname' => 'Origin$alF &il eName.jpg',                 // original filename, for files from an web url this is the web url
  'name' => 'OriginalFileName',                             // for uploaded files name will be a stripped version of 'origname', for files from a web url "name" will be the last url part ('https://flushmodules.com/css/visuals/fm-icons.png' -> 'fm-icons.png' )
  'savename' => 'OriginalFileName.jpg',                     // filename with extension
  'uploadKey' => 'avatar',                                  // represents the top level key from $_FILES of the file
  'isnew' => true,                                          // only false when file was added via phpFileHandler::add_existing_files()
  'size' => 600000,                                         // filesize in bytes
  'error' => '',                                            // the error message for invalid files
  'errorCode' => null,                                      // see phpFileHandler::add_invalid_file() for the error codes
  'isvalid' => true,                                        // whether or not the file passed the sanity checks
  'ext' => 'jpg',                                           // file extension
  'issaved' => true                                         // file is on the server and ready for further usage
)
```

- **_add_uploaded_files()_**  
> Adds all files found in the superglobal $_FILES (upload via html form or ajax)  
> You can also set one or multiple keys (array when multiple) for strictly just adding uploaded files from these keys
```php
$phpFileHandler->add_uploaded_files( [ mixed $keys = null ] )
```

- **_add_file_from_url()_**  
> Add a file from a web url  
```php
$phpFileHandler->add_file_from_url( string $url )
```

- **_add_existing_files()_**  
> Add files which already are on the server  
> This files are pushed directly into **$phpFileHandler->Files_ready** after a successful sanity check
> Single file:		**$phpFileHandler->add_existing_files( 'Path/To/My/File.jpg' );**  
> Multiple files:	**$phpFileHandler->add_existing_files( array( 'Path/To/My/File.jpg', 'Path/To/Another/File.png' ) );**  
```php
$phpFileHandler->add_existing_files( mixed $filenames )
```

- **_save()_**  
> Saves all valid files (_which has not yet been saved!_) from **$phpFileHandler->Files_valid** to the specified folder (**$to**) and adds successfully saved files to **$phpFileHandler->Files_ready**  
> By default phpFileHandler will NOT create that folder for you, nevertheless you can pass **true** as second argument to allow folder creation.  
> Same for file overriding, you can pass **true** as third argument to enable this behaviour  
> You can also save single files from **$phpFileHandler->Files_valid** by passing its *array index* as fourth argument.  
```php
$phpFileHandler->save( string $to [, bool $allow_dir_create = false [, bool $allow_override = false [, int $file_index = null ]]] )
```

- **_move_file()_**  
> Safely moves a file from one directory to another  
> *$file_index* An array index from **$phpFileHandler->Files_ready**
> *$allow_dir_create* Set to true to allow creating a folder if needed  
> *$allow_override* Set to true to allow file overwrite  
> *$copy* Set to true to copy the file to the given location by respecting *$allow_dir_create* & *$allow_override*
```php
phpFileHandler::move_file( int $file_index, string $to [, bool $allow_dir_create = false [, bool $allow_override = false [, bool $copy = false ]]] )
```

- **_remove_added_files()_**  
> Removes all added files
```php
$phpFileHandler->remove_added_files()
```

### Image Handling / Manipulation

Supported image file types for image Handling are:  
**_jpg (jpeg), gif, png, bmp, wbmp,  
gd, gd2, webp, xbm_**

_Note:_  
*bmp* is just supported in PHP 7 >= 7.2.0  
*webp* is just supported in PHP 5 >= 5.5.0, PHP 7

- **_thumb()_**  
> Creates a thumbnail for every image file in **$phpFileHandler->Files_ready** and saves it in the same directoy.  
> *$size* The size used to generate the thumbnail
> *$type*: - '' propotional scale down with *$size* defining the vertical/horizontal limit in pixels  
>          - 'iso' central isometric image crop *$size* x *$size*  
> *$prefix* Set a string which is inserted between the extension and filename (_'img.jpg' -> 'img_thumb.jpg'_)  
> *$allowGrowth* Defines Whether or not the thumbnail can be bigger than the original  
> *$to* Sets a directoy where to save the thumbnail  
> *$allow_dir_create* Wherter or not to allow folder creation (only applies when $to is set)  
> *$file_index* An array index from **$phpFileHandler->Files_ready**
```php
$phpFileHandler->thumb( int $size [, string $type = '' [, string $prefix = '_thumb' [, bool $allowGrowth = false [, string $to = null [, bool $allow_dir_create = false [, mixed $file_index = null ]]]]]] )
```

- **_resize()_**  
> Resizes every image file in **$phpFileHandler->Files_ready** propotional
```php
$phpFileHandler->resize( int $size [, string $to = null [, string $prefix = '' [, mixed $file_index = null ]]] )
```

- **_convert_image()_**  
> Converts every image file in **$phpFileHandler->Files_ready** to the given output image type  
> The original image is overwritten by default!
> Set *$keepOriginal* to true if you do not want this behaviour
```php
$phpFileHandler->convert_image( string $output_type [, bool $keepOriginal = false [, mixed $file_index = null ]] )
```

- **_put_watermark()_**  
> Puts an watermark on top of every image file in **$phpFileHandler->Files_ready**
> *$opacity* The watermark's opacity can be set as float in a range of 0.00(_fully transparent_) to 1.00(_fully visible_)  
> *$position* 'center'(_default_), 'top', 'left', 'right', 'bottom', 'top left', 'top right', 'bottom left', 'bottom right'
```php
$phpFileHandler->put_watermark( string $watermark [, float $opacity = 0.5 [, string $position = 'center' [, int $offsetX = 0 [, int $offsetY = 0 [, mixed $file_index = null ]]]]] )
```

### static utility functions

- **_uniqString()_**  
> Generate a unique alphanumeric string  
> *$length* Sets the string output length in characters
```php
phpFileHandler::uniqString( int $length = 12 )
```

- **_delete_folder()_**  
> Safely deletes a folder
```php
phpFileHandler::delete_folder( string $dir )
```

- **_emtpy_folder()_**  
> Safely empties a folder  
> *$keepSubFolders* Set to true if you do not want the subfolders to be deleted
> *$fileExceptions* You can pass an array containing extension, files having such an extension will NOT be deleted
```php
phpFileHandler::emtpy_folder( string $dir [, bool $keepSubFolders = false [, mixed $fileExceptions = null ]] )
```

- **_guess_fileextension()_**  
> Guesses the file's extension  
> Returns the file extension or false on failure  
> Note: this will not guarantee to output the real file extension!
```php
phpFileHandler::guess_fileextension( [ string $filename = null [, string $filecontent = null ]] )
```

- **_getFileSignature()_**  
> Gets the file signature in hexadecimal  
> By default it will start at the first byte of the file and will output 4 array elements  
> You can adjust this by passing an *$index* for the starting position and a *$count*
> Returns an array containing an hexadecimal representation per byte per element 
> Note: this function uses *bin2hex()* so only files which contain plain binary data will be represented correctly
```php
phpFileHandler::getFileSignature( string $filecontent [, int $index = 0 [, int $count = 4 ]] )
```

- **_compareFileSignature()_**  
> Compares 2 file signatures by simply comparing each same array index
> You can also just pass the *$comparison* and *$filename* so the signature of *$filename* will be gernerated by *phpFileHandler::getFileSignature()*
> Returning true on exact array match
```php
phpFileHandler::compareFileSignature( array $comparison [, array $signature = null [, string $filename = null [, int $index = 0 ]]] )
```

- **_is_XXX()_**  
> Those functions determine whether or not the file's signature is correct  
> Note: this does not guarantee that the file's content represent correct data!
```php
phpFileHandler::is_jpg( [ string $filename = null [, array $signature = null ]] )
phpFileHandler::is_gif( [ string $filename = null [, array $signature = null ]] )
phpFileHandler::is_png( [ string $filename = null [, array $signature = null ]] )
phpFileHandler::is_bmp( [ string $filename = null [, array $signature = null ]] )
phpFileHandler::is_webp( [ string $filename = null [, array $signature = null ]] )
phpFileHandler::is_webm( [ string $filename = null [, array $signature = null ]] )
phpFileHandler::is_gzip( [ string $filename = null [, array $signature = null ]] )
phpFileHandler::is_7zip( [ string $filename = null [, array $signature = null ]] )
phpFileHandler::is_rar( [ string $filename = null [, array $signature = null ]] )
phpFileHandler::is_tif( [ string $filename = null [, array $signature = null ]] )
phpFileHandler::is_pdf( [ string $filename = null [, array $signature = null ]] )
phpFileHandler::is_wav( [ string $filename = null [, array $signature = null ]] )
phpFileHandler::is_avi( [ string $filename = null [, array $signature = null ]] )
phpFileHandler::is_tar( [ string $filename = null [, array $signature = null ]] )
phpFileHandler::is_xml( [ string $filename = null [, array $signature = null ]] )
phpFileHandler::is_mp3( [ string $filename = null [, array $signature = null ]] )
phpFileHandler::is_wmv( [ string $filename = null [, array $signature = null ]] )
phpFileHandler::is_wma( [ string $filename = null [, array $signature = null ]] )
```
