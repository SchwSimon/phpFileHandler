<?php
		
/***
 * phpFileHandler - All in one PHP file handling class
 */
class phpFileHandler {

	/**
	 * The phpFileHandler Version number
	 * @var string
	 */
	public $Version = '0.1';

	/**
	 * Is the mbstring extension loaded?
	 * @var boolean
	 */
	public $is_mbstring_ext;

	/**
	 * Got phpFileHandler->add_uploaded_files() called?
	 * @var boolean
	 */
	public $is_add_uploaded = false;

	/**
	 * The maximum allowed filesize in bytes
	 * '0' means no limit, NOTE that this does NOT represent the 'post_max_size' or 'upload_max_filesize' set in the php.ini,
	 * $_POST and $_FILES superglobals are empty if the the maximum '-> POST size is exceeded !
	 * @var integer
	 */
	public $MaxFileSize = 0;

	/**
	 * The allowed file types
	 * @var array
	 */
	public $AllowedFileTypes;

	/**
	 * An error String containing information about non fatal errors like invalid file uploads
	 * Secure for user output
	 * @var string
	 */
	//public $Error = '';

	/**
	 * File packs
	 * @var array $Files All added files
	 * @var array $Files_valid The valid sanitized files ready for further phpFileHandler usage
	 * @var array $Files_invalid The invalid files which could not passed the sanitize checks
	 */
	public $Files = array();
	public $Files_valid = array();
	public $Files_invalid = array();

	/**
	 * File counters
	 * @var integer $Files_count added files count
	 * @var integer $Files_valid_count valid file count
	 * @var integer $Files_invalid_count invalid file count
	 */
	public $Files_count = 0;
	public $Files_valid_count = 0;
	public $Files_invalid_count = 0;

	/**
	 * Allowed file types presets
	 * @var array FTY_IMAGES_COMMON typical for default image only uploads
	 * @var array FTY_IMAGES_GD all types processable with the PHP GD extension
	 * @var array FTY_VIDEO_COMMON typical for default video only uploads
	 * @var array FTY_MEDIA_COMMON common image & videos
	 */
	const FTY_IMAGES_COMMON = ['jpg','gif','png'];
	const FTY_IMAGES_GD = ['jpg','gif','png','gd2','bmp','wbmp','webp','xbm','xpm'];
	const FTY_VIDEO_COMMON = ['mp4','webm'];
	const FTY_MEDIA_COMMON = ['jpg','gif','png','mp4','webm'];

	/**
	 * Error severity: 
	 * @var integer ERR_FILE_UPLOAD_SIZE file exceeding the maximum filesize limit
	 * @var integer ERR_FILE_TYPE filetype is not allowed
	 * @var integer ERR_FILE_URL_SERVER could not reach or read the file from the URL location
	 */
	const ERR_FILE_UPLOAD_SIZE = 0;
	const ERR_FILE_TYPE = 1;
	const ERR_FILE_URL_READ = 2;

	/**
	 * Constructor
	 */
	public function __construct() {
		// check if the mbstring extension is loaded
		$this->is_mbstring_ext = extension_loaded( 'mbstring' );
	}

	/**
	 * Return an array containing information about the phpFileHandler and its current confirguration
	 * @param boolean $dump For direct browser output set to TRUE
	 */
	public function info( $dump = false ) {
		$info = array(	'Version' => $this->Version,
							'MaxFileSize' => $this->MaxFileSize . ' bytes ("0" means unlimited)' );
		return ( $dump ) ? var_dump( $info ) : $info;
	}

	/**
	 * Set the maximum filesize
	 * @param integer $size The maximum filesize in megabytes, or "0" for no limit
	 * @param boolean $isMB Wheter or not $size is in megabyte format
	 * @throws Exception
	 */
	public function setMaxFileSize( $size, $isMB = true ) {
		if ( $size < 0 ) {
			throw new Exception( 'phpFileHandler->setMaxSize() @var $size must be greater or equal "0"' );
		}
		// convert to byte if $isMB = true
		$this->MaxFileSize = ( $isMB ) ? $size * 100000 : $size;
	}

	/**
	 * Sets the allowed file types, which counts as valid for the next file adds
	 * @param array $types pass False for no restriction
	 */
	public function setAllowedFileTypes( $types ) {
		if ( $types === false ) {
			// allow all filetypes
			$this->AllowedFileTypes = null;
		} else {
			$types = (array)$types;
			for( $i = 0, $count = count( $types ); $i < $count; $i++ ) {
				$types[$i] = (string)$types;
			}
			$this->AllowedFileTypes = $types;
		}
	}

	/**
	 * Generate a unique random string (for good uniqueness the $length should be atleast 9)
	 * @param integer $length The length of the output string, must be greater than 0
	 * @throws Exception
	 */
	public static function uniqString( $length = 9 ) {
		if ( $length < 1 ) {
			// if $length is less than 1 the output string would be ~9 characters long
			throw new Exception( 'phpFileHandler::uniqString() @var $length must be greater than "0"' );
		}
		$uString = base_convert( microtime( true ), 10, 36 );
		$padLength = $length - strlen( $uString );

			// if $padLength is greater than 0, extend to string to the given $length using random byte generator combined with hex conversion
			// else substring it to the given $length
		if ( $padLength > 0 ) {
			if ( version_compare( PHP_VERSION, '7.0.0' ) >= 0 ) {
				$rBytes = random_bytes( ceil( $padLength / 2 ) );
			} else {
				$rBytes = openssl_random_pseudo_bytes( ceil( $padLength / 2 ) );
			}
			$uString = str_pad( $uString, $length, bin2hex( $rBytes ) );
		} else {
			$uString = substr( $uString, -$length );
		}
		return $uString;
	}

	/**
	 * Adds all files from the PHP superglobal $_FILES
	 */
	public function add_uploaded_files() {
		if ( $this->is_add_uploaded ) {
			throw new Exception( 'phpFileHandler->add_uploaded_files() already got called once.' );
		}
		$this->is_add_uploaded = true;
		foreach( $_FILES as $key => $data ) {
			$data['tmp_name'] = (array)$data['tmp_name'];
			$data['name'] = (array)$data['name'];
			$data['error'] = (array)$data['error'];
			for( $i = 0, $count = count( $data['tmp_name'] ); $i < $count; $i++ ) {
				$file = array(	'path'	=> $data['tmp_name'][$i],
									'name' => $data['name'][$i],
									'isnew' => true	);

				// php.ini -> 'upload_max_filesize' exceeded error
				if ( $data['error'][$i] === UPLOAD_ERR_INI_SIZE ) {
					$this->add_invalid_file( $file, self::ERR_FILE_UPLOAD_SIZE ); 
					continue;
				}

				// guess the image extension by checking the type or name key value
				if ( $data['type'][$i] !== '' ) {
					$ext_guess = substr( strrchr( $data['type'][$i], '/' ), 1 );
				} else if ( strpos( $data['name'][$i], '.' ) !== false ) {
					$ext_guess = substr( strrchr( $data['name'][$i], '.' ), 1 );
				} else {
					$ext_guess = '';
				}
				$file['ext'] = str_replace( 'jpeg', 'jpg', $ext_guess );

				$this->process_file_add( $file );
			}
		}
	}

	/**
	 * Add a file from a web url
	 * @param string $url The file's web url
	 */
	public function add_file_from_url( $url ) {
		$url = ( substr( $url, 0, 4 ) !== 'http' ) ? 'http://' . str_replace( '//', '', $url ) : $url;
		$file = array(	'path'	=> $url,
							'name' => substr( strrchr( $url, '/' ), 1 ),
							'isnew' => true,
							'ext' => ''	);
		$this->process_file_add( $file, true );
	}

	/**
	 * Adds files which are already existing on the Server for further processing
	 * @param string | array $filenames The file paths to add
	 */
	public function add_existing_files( $filenames ) {
		$filenames = (array)$filenames;
		for( $i = 0, $count = count( $filenames ); $i < $count; $i++ ) {
			$pathinfo = pathinfo( $filenames[$i] );
			$this->process_file_add( array(	'path'	=> $filenames[$i],
															'name' => $pathinfo['basename'],
															'isnew' => false,
															'ext' => $pathinfo['extension']	)	);
		}
	}

	/**
	 * Runs a file trough the main file checking process which is the only way to get files into @Files_valid
	 * @param array $file A phpFileHandler $file array
	 * @param boolean $isUrl True if $file['path'] is an web url
	 */
	protected function process_file_add( $file, $isUrl = false ) {
		// get the raw file content
		$byteStream = self::fileToByteStream( $file['path'], $isUrl );

		if ( $isUrl ) {
			if ( $byteStream === false || $byteStream === '' ) {
				return $this->add_invalid_file( $file, self::ERR_FILE_URL_READ ); 
			}
			// filter the mime type out from the response headers
			for( $i = 0, $count = count( $http_response_header ); $i < $count; $i++ ) {
				if ( strpos( strtolower( $http_response_header[$i] ), 'content-type' ) !== false ) {
					$file['ext'] = explode( '/', trim( explode( ';', explode( ':', $http_response_header[$i], 2 )[1], 2 )[0] ), 2 )[1];
				}
			}
		}

		// get the file size using mb_strlen or strlen (fallback when php_mbstring module is not loaded)
		// Note that this is only accurate when using mb_strlen because of mbstring.func_overload (which has been DEPRECATED as of PHP 7.2.0)
		$filesize = ( $this->is_mbstring_ext ) ? mb_strlen( $byteStream, '8bit' ) : strlen( $byteStream );
		if ( $this->MaxFileSize !== 0 && $filesize > $this->MaxFileSize ) {
			return $this->add_invalid_file( $file, self::ERR_FILE_UPLOAD_SIZE ); 
		}

		if ( $this->AllowedFileTypes ) {

			// check if the guessed extension is allowed
			if ( !in_array( $file['ext'], $this->AllowedFileTypes ) ) {
				return $this->add_invalid_file( $file['ext'], self::ERR_FILE_TYPE ); 
			}

			// if its a common media file make a hard filetype check
			if ( !self::check_mediafile( $byteStream, $file['ext'] ) ) {
				return $this->add_invalid_file( $file, self::ERR_FILE_TYPE ); 
			}
		}

		if ( $isUrl ) {
			// generate a random temporary name for the file and save it to the system's default temp directory
			$file['path'] = tempnam( sys_get_temp_dir(), self::uniqString( 24 ) );
			file_put_contents( $file['path'], $byteStream );
		}

		$file['size'] = $filesize;
		$this->add_valid_file( $file );
	}

	public function save( $path, $file = null ) {

	}

	/**
	 * Checks the common media files ( @see phpFileHandler::FTY_MEDIA_COMMON )
	 * if the first bytes notation of the file match with the given filetype
	 * @param string $byteStream A raw file content as string
	 * @param string $type An error severity constant
	 * @return boolean | integer if the file does not prove as common media file return will be positive integer
	 * Note that it is possible when $type is null, @return could be false when the first byte equals to one of the cases below
	 * or even in the very unlikely case true when all first bytes checked equals
	 */
	public static function check_mediafile( $byteStream, $type = '' ) {
		$hexArr = self::byteStreamToHexCheckArray( $byteStream, $type );
		if ( $type !== '' ) {
			switch( $type ) {
				case 'jpg': { return self::is_jpg( null, $hexArr ); } break;
				case 'gif': { return self::is_gif( null, $hexArr ); } break;
				case 'png': { return self::is_png( null, $hexArr ); } break;
				case 'mp4': { return self::is_mp4( null, $hexArr ); } break;
				case 'webm': { return self::is_webm( null, $hexArr ); } break;
				default: return 1;
			}
		} else {
			switch( $hexArr[0] ) {
				case 'FF': { return self::is_jpg( null, $hexArr ); } break;
				case '47': { return self::is_gif( null, $hexArr ); } break;
				case '89': { return self::is_png( null, $hexArr ); } break;
				default: {
					$hexArr = self::byteStreamToHexCheckArray( $byteStream, 'mp4' );
					switch( $hexArr[0] ) {
						case '66': { return self::is_mp4( null, $hexArr ); } break;
						case '1A': { return self::is_webm( null, $hexArr ); } break;
						default: return 1;
					}
				}
			}
		}
	}

	/**
	 * Gets the raw file byte data as string
	 * @param string $filename Path to the file
	 * @return string The raw byte stream from the given file as string
	 */
	protected static function fileToByteStream( $filename, $isUrl = false ) {
		if ( $isUrl ) {
			try {
				$byteStream = file_get_contents(
					$filename, false,
					stream_context_create(
						array( 'http' => array(
							'method' => 'GET',
							'timeout' => 10
						))
					), 0, $this->MaxFileSize
				);
			} catch( Exception $exc ) {
				return false;
			}
		} else {
			if ( !file_exists( $filename ) ) {
				throw new Exception( 'The file: ' . htmlspecialchars( $filename ) . ' does not exist.' );
			}
			$byteStream = file_get_contents( $filename );
		}
		return $byteStream;
	}

	/**
	 * Adds an invalid file with its error to $Files_invalid
	 * @param array $file
	 * @param integer $error An error severity constant
	 */
	protected static function byteStreamToHexCheckArray( $byteStream, $type = null ) {
		$hexArr = array();
		for( ($i = ( $type === 'mp4' || $type === 'webm' ) ? 4 : 0); $i < 4; $i++ ) {
			$hexArr[] = strtoupper( bin2hex( $byteStream[$i] ) );
		}
		return $hexArr;
	}

	/**
	 * Checks if the given file or hexadecimal array agree with the first byte notation of the following media types:
	 * * jpg, gif, png, mp4, webm
	 * @param string $filename The path to the file
	 * @param array $hexArr An array containing hexadecimal representation of bytes from a file
	 */
	public static function is_jpg( $filename = null, $hexArr = null  ) {
		if ( $filename ) {
			$hexArr = self::byteStreamToHexCheckArray( self::fileToByteStream( $filename ), 'jpg' );
		}
		return ( $hexArr[0] === 'FF' && $hexArr[1] === 'D8' && $hexArr[2] === 'FF' ) ? true : false;
	}
	public static function is_gif( $filename = null, $hexArr = null  ) {
		if ( $filename ) {
			$hexArr = self::byteStreamToHexCheckArray( self::fileToByteStream( $filename ), 'gif' );
		}
		return ( $hexArr[0] === '47' && $hexArr[1] === '49' && $hexArr[2] === '46' ) ? true : false;
	}
	public static function is_png( $filename = null, $hexArr = null  ) {
		if ( $filename ) {
			$hexArr = self::byteStreamToHexCheckArray( self::fileToByteStream( $filename ), 'png' );
		}
		return ( $hexArr[0] === '89' && $hexArr[1] === '50' && $hexArr[2] === '4E' ) ? true : false;
	}
	public static function is_mp4( $filename = null, $hexArr = null  ) {
		if ( $filename ) {
			$hexArr = self::byteStreamToHexCheckArray( self::fileToByteStream( $filename ), 'mp4' );
		}
		return ( $hexArr[0] === '66' && $hexArr[1] === '74' && $hexArr[2] === '79' && $hexArr[3] === '70' ) ? true : false;
	}
	public static function is_webm( $filename = null, $hexArr = null  ) {
		if ( $filename ) {
			$hexArr = self::byteStreamToHexCheckArray( self::fileToByteStream( $filename ), 'webm' );
		}
		return ( $hexArr[0] === '1A' && $hexArr[1] === '45' && $hexArr[2] === 'DF' && $hexArr[3] === 'A3' ) ? true : false;
	}

	/**
	 * Adds an invalid file with its error to $Files_invalid
	 * @param array $file
	 * @param integer $error An error severity constant
	 */
	protected function add_invalid_file( $file, $error ) {
		switch( $error ) {
			case 0: { $errorMsg = 'Exceeding the maximum upload file size. '; } break;
			case 1: { $errorMsg = 'Filetype not allowed.'; } break;
			case 2: { $errorMsg = 'Could not fetch the file from the web address.'; } break;
		}
		$file['error'] = $errorMsg;
		$file['isvalid'] = false;

		$this->Files_invalid[] = $file;
		$this->Files_invalid_count++;
		$this->add_file( $file );

		// delete the file if it is a new one
		if ( $file['isnew'] ) {
			unlink( $file['path'] );
		}
	}

	/**
	 * Adds an valid file to $Files_valid
	 * @param array $file
	 */
	protected function add_valid_file( $file ) {
		$file['error'] = '';
		$file['isvalid'] = true;

		$this->Files_valid[] = $file;
		$this->Files_valid_count++;
		$this->add_file( $file );
	}

	/**
	 * Adds an added file regardless of whether valid or invalid to $Files
	 * @param array $file
	 */
	protected function add_file( $file ) {
		$this->Files[] = $file;
		$this->Files_count++;
	}	

}

