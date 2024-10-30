<?php

namespace Codevision\Util;

use Codevision\Environment;

class Helper {

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

    public static function setEnvironment( Environment $environment, $name = 'default' ) {

        self::createLogger( $name, $environment->getBasePath(), false );
    }

    private static function createLogger( $name = 'default', $base_dir = false, $stdout = false ) {

        if ( array_key_exists( $name, self::$logger ) ) {
            return self::$logger[ $name ];
        }

        $logger = new \Monolog\Logger( $name );

        $base_dir = self::ensureTrailingBackslash( self::baseLogDir( $base_dir ) );

        $stream = new \Monolog\Handler\RotatingFileHandler( $base_dir . "log/${name}.log", 5, \Monolog\Logger::DEBUG, true );

        $stream->setFormatter( new \Monolog\Formatter\LineFormatter( "%datetime% - %level_name% - %message%\n", "Y-m-d H:i:s" ) );
        $stream->pushProcessor( new \Monolog\Processor\IntrospectionProcessor( 'debug' ) );

        $stream->pushProcessor( new \Monolog\Processor\UidProcessor() );
        $stream->pushProcessor( new \Monolog\Processor\PsrLogMessageProcessor() );

        $stream->pushProcessor( function( $record ) {

            /**
             * @var $timestamp \DateTimeImmutable
             */
            $timestamp = $record[ 'datetime' ];

            $timestamp->setTimezone( new \DateTimeZone( 'Europe/Berlin' ) );

            $record[ 'datetime' ] = $timestamp;

            return $record;
        } );


        $logger->pushHandler( $stream );

        if ( $stdout ) {
            $sysout = new \Monolog\Handler\StreamHandler( 'php://stdout', \Monolog\Logger::DEBUG );
            $sysout->setFormatter( new \Monolog\Formatter\LineFormatter( "%datetime% - %level_name% - %message%\n", "Y-m-d H:i:s" ) );
            $sysout->pushProcessor( new \Monolog\Processor\IntrospectionProcessor( 'debug' ) );
            $sysout->pushProcessor( new \Monolog\Processor\UidProcessor() );
            $sysout->pushProcessor( new \Monolog\Processor\PsrLogMessageProcessor() );

            $sysout->pushProcessor( function( $record ) {

                /**
                 * @var $timestamp \DateTimeImmutable
                 */
                $timestamp = $record[ 'datetime' ];

                $timestamp->setTimezone( new \DateTimeZone( 'Europe/Berlin' ) );

                $record[ 'datetime' ] = $timestamp;

                return $record;
            } );

            $logger->pushHandler( $sysout );
        }

        self::$logger[ $name ] = $logger;

        return $logger;
    }

    public static function getLogger( $name = 'default', $base_dir = false, $stdout = false ) {

        return self::createLogger( $name, $base_dir, $stdout );
    }

    public static function log( $msg, $type = 'info', $name = 'default' ) {

        if ( is_array( $msg ) ) {
            $msg = json_encode( $msg, JSON_PRETTY_PRINT );
        }

        if ( array_key_exists( 'plugin', self::$logger ) ) {

            $logger = self::$logger[ 'plugin' ];

            $logger->$type( $msg );

            return;
        }

        $logger = self::getLogger( $name );

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


}
