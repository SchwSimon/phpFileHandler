# phpFileHandler

All in one PHP file handling class

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

$phpFileHandler->Files_valid          // The valid files array
$phpFileHandler->Files_valid_count    // Valid files count
$phpFileHandler->Files_invalid        // The invalid files
$phpFileHandler->Files_invalid_count  // Invalid files count
$phpFileHandler->File                 // All uploaded files 
$phpFileHandler->Files_count          // Uploaded files count



```
