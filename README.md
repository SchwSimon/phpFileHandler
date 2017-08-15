# phpFileHandler

All in one PHP file handling class

(Started 13.08.2017, not yet finished with the initial build)

## File sanity

Image files are NOT checked with the use of exif_imagetype.
Extensions are guessed with their file signature!
Following file types can be detected:

jpg (jpeg), gif, png, bmp,
mp4, webm, webp, gzip, 7zip,
rar, exe, tif, tiff, pdf, wav,
avi, xml, mp3, wmv, wma

If the filetype could not be recognized, the mime string or at last the original name extension will be taken.
You can enable strict file type checking, so the added files must meet one of the upper file signatures to pass.
$phpFileHandler->setStrictFilecheck( true );

## How to use (minimum)

```php
<?php
require 'phpFileHandler.php';

$phpFileHandler = new phpFileHandler;  // create phpFileHandler object
$phpFileHandler->add_uploaded_files();  // Add all uploaded files from the $_FILES superglobal 
$phpFileHandler->save( '/Path/To/Save/' ); // Save all valid files with a new unique name (12 characters long) to the given location 

```

## How to use (all options)

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
$phpFileHandler->setMaxFileSize( 8000000, false ); // optioanl you can pass $filesize in bytes with the second parameter set to false

// set the allowed filetypes for the next file adds by passing an array with the allowed extensions
// for default all filetypes are allowed
$phpFileHandler->setAllowedFileTypes( ['jpg','gif','png'] );
$phpFileHandler->setAllowedFileTypes( phpFileHandler::FTY_IMAGES_COMMON ); // or use the phpFileHandler presets
$phpFileHandler->setAllowedFileTypes( false );  // pass false to reset to default

// adds all files which were uploaded via html form or ajax to phpFileHandler
$phpFileHandler->add_uploaded_files();

// adds a file from a given web url to phpFileHandler
$phpFileHandler->add_file_from_url( 'https://flushmodules.com/data/users/1/5wi77gugko3q.png' );

// add already existings files on the server to phpFileHandler
$phpFileHandler->add_existing_files( 'C:/Apache24/htdocs/domain/data/my_file.txt' );

// File key infos
// not all of the keys are availible for invalid files
// array(
//  "path" => *full file location*,           (string)
//  "origname" => *original filename*,        (string) // the full url for files added via ->add_file_from_url()
//  "name" => *filename*,                     (string) // for new files the temporary filename (before ->save() got called) is NOT "name"
//  "savename" => *filename with extension*,  (string) // before ->save() got called only for ->add_existing_files() not null
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

// by default the save filenames will be a unique string (with a length of 12 characters)
// you can change the length of the filesnames
$phpFileHandler->setUniqFilenames( true, 20 );  // filenames will now be 20 characters long
$phpFileHandler->setUniqFilenames( false ); // or do not generate unique filenames, now the filenames will keep their original names

// 
$phpFileHandler->save( 'C:/The/Save/Path/' ); // save all valid files (phpFileHandler->Files_valid) to the given location
$phpFileHandler->save( 'C:/The/Save/Path/New/', true ); // allow phpFileHandler to create non existing paths (recursive)
$phpFileHandler->save( 'C:/The/Save/Path/New/', true, 0 ); // save only the file on index '0' to from phpFileHandler->Files_valid
$phpFileHandler->save( 'C:/The/Save/Path/New/', true, 0, 'myfile' ); // " and name it "myfile"


// Image handling next...

```
