# phpFileHandler

All in one PHP file handling class

(Started 13.08.2017, not yet finished with the starting build)

## File sanity

Image files are NOT checked with the use of exif_imagetype!
The following (common) media files are checked with their first bytes notation:
 - jpg (jpeg)
 - gif
 - png
 - mp4
 - webm

## How to use

```php
<?php
require 'phpFileHandler.php';

// Adding uploaded files, uploaded via HTML form or JS Ajax
// regardless of whether multiple or one single file
// @add_uploaded_files() will check the PHP superglobal $_FILES for uploaded files
$phpFileHandler = new phpFileHandler;

// optional filesize limit, if not set the php.ini 'upload_max_filesize' will be used.
// @param integer $filesize in Megabyte
$phpFileHandler->setMaxFileSize( 8 );
// optioanl you can pass $filesize in bytes with the second parameter set to false
$phpFileHandler->setMaxFileSize( 8000000, false );

// set the allowed filetypes for the next file adds by passing an array with the allowed extensions
// for default all filetypes are allowed
$phpFileHandler->setAllowedFileTypes( ['jpg','gif','png'] );
// or use the phpFileHandler presets
$phpFileHandler->setAllowedFileTypes( phpFileHandler::FTY_IMAGES_COMMON );
// pass false to reset to default
$phpFileHandler->setAllowedFileTypes( false );  

// adds all files which were uploaded via html form or ajax to phpFileHandler
$phpFileHandler->add_uploaded_files();

// adds a file from a given web url to phpFileHandler
$phpFileHandler->add_file_from_url( 'https://flushmodules.com/data/users/1/5wi77gugko3q.png' );

// add already existings files on the server to phpFileHandler
$phpFileHandler->add_existing_files( 'C:/Apache24/htdocs/domain/data/my_file.txt' );

// File key infos
// array(
//  "path" => *current file location*,        (string)
//  "name" => *original filename*,            (string)
//  "isnew" => *new file?*,                   (boolean)
//  "size" => *filesize*,                     (integer)
//  "error" => *invalid file error message*,  (string)
//  "isvalid" => *is a valid file*,           (boolean)
//  "ext" => *file extension*                 (string)
// );
$phpFileHandler->Files_valid          // The valid files array
$phpFileHandler->Files_valid_count    // Valid files count
$phpFileHandler->Files_invalid        // The invalid files
$phpFileHandler->Files_invalid_count  // Invalid files count
$phpFileHandler->File                 // All uploaded files 
$phpFileHandler->Files_count          // Uploaded files count



```
