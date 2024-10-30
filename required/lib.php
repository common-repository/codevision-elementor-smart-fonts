<?php

/**
 * Every PHP File within the Lib Folder
 */
foreach ( glob( __DIR__ . '/../lib/*.php' ) as $lib ) {
	require_once $lib;
}