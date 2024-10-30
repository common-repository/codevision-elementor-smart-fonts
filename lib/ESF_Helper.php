<?php

class ESF_Helper {

	// Singleton Loggers
	private static $logger = [];

	public static function baseLogDir( $base_dir = false ) {

		if ( $base_dir ) {
			return rtrim( $base_dir, '/' ) . '/';
		}

		if ( defined( 'LOG_DIR' ) ) {
			$base_dir = rtrim( LOG_DIR, '/' ) . '/';
		}

		if ( ! $base_dir ) {
			$base_dir = plugin_dir_path( dirname( __FILE__ ) );
		}

		return $base_dir;
	}

	public static function get_plugin_settings() {

		$defaults = Local_Font_Provider::get_defaults();

		$global_options = get_fields( 'option' );

		if ( ! is_array( $global_options ) ) {
			$global_options = [];
		}

		$global_options = array_filter( $global_options, function ( $key ) {
			return strpos( $key, Local_Font_Provider::PREFIX ) === 0;

		}, ARRAY_FILTER_USE_KEY );

		// Remove the prefix from options
		$options  = [];
		$_options = array_merge( $defaults, $global_options );
		foreach ( $_options as $key => $value ) {

			$key = str_replace( Local_Font_Provider::PREFIX, '', $key );

			$options[ $key ] = $value;
		}

		return $options;
	}

	public static function getLogger( $name = 'default', $base_dir = false, $stdout = false ) {

		$logger = new Monolog\Logger( $name );

		$base_dir = self::ensureTrailingBackslash( self::baseLogDir( $base_dir ) );

		$stream = new \Monolog\Handler\RotatingFileHandler( $base_dir . "log/${name}.log", 5, \Monolog\Logger::DEBUG, true );
		@chown( $stream->getUrl(), 'www-data' );

		$stream->setFormatter( new \Monolog\Formatter\LineFormatter( "%datetime% - %level_name% - %message%\n", "Y-m-d H:i:s" ) );
		$stream->pushProcessor( new \Monolog\Processor\IntrospectionProcessor( 'debug' ) );

		$stream->pushProcessor( new \Monolog\Processor\UidProcessor() );
		$stream->pushProcessor( new \Monolog\Processor\PsrLogMessageProcessor() );

		$stream->pushProcessor( function ( $record ) {

			/**
			 * @var $timestamp DateTimeImmutable
			 */
			$timestamp = $record['datetime'];

			$timestamp->setTimezone( new \DateTimeZone( 'Europe/Berlin' ) );

			$record['datetime'] = $timestamp;

			return $record;
		} );


		$logger->pushHandler( $stream );

		if ( $stdout ) {
			$sysout = new \Monolog\Handler\StreamHandler( 'php://stdout', \Monolog\Logger::DEBUG );
			$sysout->setFormatter( new \Monolog\Formatter\LineFormatter( "%datetime% - %level_name% - %message%\n", "Y-m-d H:i:s" ) );
			$sysout->pushProcessor( new \Monolog\Processor\IntrospectionProcessor( 'debug' ) );
			$sysout->pushProcessor( new \Monolog\Processor\UidProcessor() );
			$sysout->pushProcessor( new \Monolog\Processor\PsrLogMessageProcessor() );

			$sysout->pushProcessor( function ( $record ) {

				/**
				 * @var $timestamp DateTimeImmutable
				 */
				$timestamp = $record['datetime'];

				$timestamp->setTimezone( new \DateTimeZone( 'Europe/Berlin' ) );

				$record['datetime'] = $timestamp;

				return $record;
			} );

			$logger->pushHandler( $sysout );
		}


		self::$logger[ $name ] = $logger;

		return $logger;
	}

	public static function log( $msg, $type = 'info' ) {

		if ( is_array( $msg ) ) {
			$msg = json_encode( $msg, JSON_PRETTY_PRINT );
		}

		if ( array_key_exists( 'plugin', self::$logger ) ) {

			$logger = self::$logger['plugin'];

			$logger->$type( $msg );

			return;
		}

		$logger = self::getLogger( ESF_SLUG );

		$logger->$type( $msg );
	}

	public static function filesize( $file, $is_file = true ) {

		if ( $is_file ) {
			$size = filesize( $file );
		} else {
			$size = $file;
		}

		$units = array( 'B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB' );
		$power = $size > 0 ? floor( log( $size, 1024 ) ) : 0;

		return number_format( $size / pow( 1024, $power ), 2, '.', ',' ) . ' ' . $units[ $power ];
	}

	public static function ensureTrailingBackslash( $path, $dir_separator = '/' ) {
		return rtrim( $path, $dir_separator ) . $dir_separator;
	}


	public static function sanitize( $key, $array, $default = '' ) {
		if ( ! array_key_exists( $key, $array ) ) {
			return $default;
		}

		return $array[ $key ];
	}


	public static function get_cache_folder( $add = [] ) {
		$parts = [
			WP_CONTENT_DIR,
			'esf-cache'
		];

		$parts = array_merge( $parts, $add );

		return self::ensureTrailingBackslash( implode( '/', $parts ) );
	}

	public static function get_cache_url( $add = [] ) {
		$parts = [
			content_url(),
			'esf-cache'
		];

		$parts = array_merge( $parts, $add );

		return self::ensureTrailingBackslash( implode( '/', $parts ) );
	}

	public static function get_font_file( $attachment_id ) {

		if ( ! $attachment_id ) {
			return false;
		}

		try {
			$client = new GuzzleHttp\Client();

			$file_data = get_attached_file( $attachment_id );

			if ( ! $file_data ) {
				return false;
			}

			$filename = pathinfo( $file_data, PATHINFO_BASENAME );

			$response = $client->post( 'https://webfont.codevision.io/', [
				'multipart' => [
					[
						'name'     => 'fonts[files][]',
						'contents' => file_get_contents( $file_data ),
						'filename' => $filename
					]
				],
			] );

			if ( $response->getStatusCode() !== 200 ) {
				return false;
			}

			return $response->getBody()->getContents();

		} catch ( Exception $e ) {
			self::log( $e );
		}

		return false;
	}

	/**
	 * Remove an directory with all its files in it
	 *
	 * @param $path
	 * @param bool $is_recursive
	 * @param array $skip
	 */
	public static function rmdir( $path, $is_recursive = true, $skip = [] ) {

		if ( ! $path ) {
			return;
		}

		if ( $path === '/' ) {
			return;
		}

		$path = self::ensureTrailingBackslash( $path );

		$files              = glob( $path . '*' );
		$skipped_file_found = false;

		foreach ( $files as $file ) {

			if ( self::contains( $skip, $file ) ) {

				$skipped_file_found = true;

				continue;
			}

			if ( $is_recursive && is_dir( $file ) ) {

				self::rmdir( $file, $is_recursive );

				continue;
			}

			unlink( $file );

		}

		// Do not delete the folder, if at least one skipped file has been found
		if ( $skipped_file_found ) {
			return;
		}

		rmdir( $path );

		return;
	}


	private static function contains( $array, $key ) {

		foreach ( $array as $arr ) {
			if ( strpos( $key, $arr ) !== false ) {
				return true;
			}
		}

		return false;
	}
}