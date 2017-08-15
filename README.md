# phpFileHandler

All in one PHP file handling class  
- Including image handling!

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
	If $isMB set to false, $size can be passed in byte format
	
- **_setAllowedFileTypes()_**	// default: false
	Set allowed file types, if not set (or false is passed) all filetypes are allowed
	Example for 'jpg' and 'png' only: ->setAllowedFileTypes( ['jpg','png'] );
	Presets can be used:	- phpFileHandler::FTY_IMAGES_COMMON // ['jpg','gif','png']
												- phpFileHandler::FTY_IMAGES_GD // ['jpg','gif','png','gd','gd2','bmp','wbmp','webp','xbm','xpm']
												- phpFileHandler::FTY_VIDEO_COMMON // ['mp4','webm']
												- phpFileHandler::FTY_MEDIA_COMMON // ['jpg','gif','png','mp4','webm']
	
- **_setStrictFilecheck()_**	// default: false
	If set to true only files can pass which signature can be identified by phpFileHandler::guess_fileextension()
	This option does NOT negate phpFileHandler::setAllowedFileTypes()
	Note: Files added from phpFileHandler::add_existing_files() are NOT affected
	
- **_setUniqFilenames()_**	// default: true
	If set to true, the files which are saved via phpFileHandler::save() will become a unique filename
	generated with phpFileHandler::uniqString()
	phpFileHandler::save() is NOT affected from this option WHEN:
	called with $file_index AND $name

- **_setUniqFilenameLength()_** // default: 12
	Set the default filename length generated by phpFileHandler::uniqString() when called from phpFileHandler::save()

```php
phpFileHandler::setMaxFileSize( int $size [, (bool) $isMB = true ] )
phpFileHandler::setAllowedFileTypes( array $types )
phpFileHandler::setStrictFilecheck( (bool) $bool )
phpFileHandler::setUniqFilenames( (bool) $bool )
phpFileHandler::setUniqFilenameLength( (integer) $length = 12 )
```

### Adding files

Files can only be added via following functions.
The adding process add an file data element for each file into:
	'->	phpFileHandler::Files_valid		// files which have passed all sanity checks
		'-> phpFileHandler::Files_valid_count
	'-> phpFileHandler::Files_invalid	// files which have failed at least one sanity check
		'-> phpFileHandler::Files_invalid_count
	'-> phpFileHandler::Files					// All added files ( valid & invalid )
		'-> phpFileHandler::Files_count

A file data element looks like this:
array(
	"path" => 'C:/Apache24/htdocs/data/temp/8dsfsa98df6.jpg', // the full filepath
  "origname" => 'Origin$alF &il eName.jpg', // original filename, for files from an web url this is the web url
  "name" => 'OriginalFileName',	// for uploaded files name will be a *_stripped_* version of "origname", for files from a web url "name" will be the last url part ('https://flushmodules.com/data/image.jpg' -> 'image.jpg' )
  "savename" => 'OriginalFileName.jpg',
  "isnew" => true, // only false when file was added via phpFileHandler::add_existing_files()
  "size" => 600000, // in bytes
  "error" => '', // the error message for invalid files
  "isvalid" => true,
  "ext" => 'jpg'
);

- **_add_uploaded_files()_**
	Adds all files found in the superglobal $_FILES (upload via html form or ajax)
	
- **_add_file_from_url()_**
	Add a file from a web url

- **_add_existing_files()_**
	Add files which already are on the server
	Single file:		->add_existing_files( 'Path/To/My/File.jpg' );
	Multiple files:	->add_existing_files( array( 'Path/To/My/File.jpg', 'Path/To/Another/File.png' ) );

```php
phpFileHandler::add_uploaded_files()
phpFileHandler::add_file_from_url( (string) $url )
phpFileHandler::add_existing_files( (mixed) $filenames )
```

### A

```php


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
