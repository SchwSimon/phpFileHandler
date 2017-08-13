# phpFileHandler

All in one PHP file handling class

## How to use

```php
<?php
require 'phpFileHandler.php';

// Adding uploaded files, uploaded via HTML form or JS Ajax
// Just pass the PHP superglobal $_FILES with the given key from your HTML form (in this case the key is "files"),
// regardless of whether multiple or one single file
$phpFileHandler = new phpFileHandler;
$phpFileHandler->add_uploaded_files( $_FILES['files'] );
// OR
$phpFileHandler = new phpFileHandler( $_FILES['files'] );

```
