<?php
/**************************************************************************\
* Protean Framework                                                        *
* https://github.com/erictj/protean                                        *
* Copyright (c) 2006-2010, Loopshot Inc.  All rights reserved.             *
* ------------------------------------------------------------------------ *
*  This program is free software; you can redistribute it and/or modify it *
*  under the terms of the BSD License as described in license.txt.         *
\**************************************************************************/

// for profiling
if (PF_PROFILER) {
	$startTime = microtime(true);
}

// set timezone for PHP 5.3 and greater
date_default_timezone_set(PF_TIMEZONE);

// force internal PHP encoding to be UTF-8
mb_internal_encoding('UTF-8');
setlocale(LC_CTYPE, 'C');
header('Content-Type: text/html; charset=UTF-8');

// set script timeout to our config setting
set_time_limit(PF_SCRIPT_TIMEOUT);

// The built-in PHP5 reflection class defines this error code (-1).  we define it here.
define('E_PHP5_ERROR', -1);

// Unknown error, usually from an SQL Exception from Creole/Propel.  we define it here.
define('E_UNKNOWN_ERROR', 0);

// Added an invalid data error code.  We put it high so it won't interfere w/ PHP's built-in error codes
define('E_INSUFFICIENT_DATA', 65536);
define('E_USER_FATAL', 32767);
define('E_USER_LOG', 16383);

/*function __autoload($classname) {
$filename = str_replace ('_', '/', $classname) . '.php';
require_once $filename;
}*/

$ds = DIRECTORY_SEPARATOR;
$ps = PATH_SEPARATOR;

// load 3rd-party paths into "include_path". (If run from command line, 
	// we're probably running unit tests, so manually determine the full path
	// from __FILE__.
	if (php_sapi_name() == 'cli') {
		$explodedPath = explode($ds . 'modules', __FILE__);
		$pf_root = $explodedPath[0];
	} else {
		$pf_root = PF_ROOT_DIRECTORY;
	}

	// we save the root in a define, for use elsewhere
	define('PF_BASE', $pf_root);

	ini_set('include_path', PF_BASE . $ps . ini_get('include_path'));

	// require all interfaces here
	require_once 'modules' . $ds . 'api' . $ds . 'lib' . $ds . 'cache.interface.php';
	require_once 'modules' . $ds . 'api' . $ds . 'lib' . $ds . 'command.interface.php';
	require_once 'modules' . $ds . 'api' . $ds . 'lib' . $ds . 'observable.interface.php';
	require_once 'modules' . $ds . 'api' . $ds . 'lib' . $ds . 'observer.interface.php';

	// base classes required all over the place
	require_once 'modules' . $ds . 'api' . $ds . 'lib' . $ds . 'exception.class.php';
	require_once 'modules' . $ds . 'api' . $ds . 'lib' . $ds . 'errorhandler.class.php';
	require_once 'modules' . $ds . 'api' . $ds . 'lib' . $ds . 'debugstack.class.php';
	require_once 'modules' . $ds . 'api' . $ds . 'lib' . $ds . 'factory.class.php';
	require_once 'modules' . $ds . 'api' . $ds . 'lib' . $ds . 'command.class.php';
	require_once 'modules' . $ds . 'api' . $ds . 'lib' . $ds . 'htmllogger.class.php';
	require_once 'modules' . $ds . 'api' . $ds . 'lib' . $ds . 'mailer.class.php';
	require_once 'modules' . $ds . 'registration' . $ds . 'lib' . $ds . 'user.class.php';

	// set error handling overrides
	$pf_handler = new PFErrorHandler();
	// set_error_handler(array($pf_handler, 'ErrorHandler'), E_ALL | E_STRICT); // (turn on strict when PEAR is PHP5'ed)
	set_error_handler(array($pf_handler, 'errorHandler'), E_ALL);
	set_exception_handler(array($pf_handler, 'exceptionHandler'));
	ini_set('display_errors', false);
	ini_set('html_errors', false);

	// set up observers here
	PFDebugStack::getInstance()->attach(new PFHTMLLogger());

	// require all singletons here
	require_once 'modules' . $ds . 'api' . $ds . 'lib' . $ds . 'language.class.php';  
	require_once 'modules' . $ds . 'api' . $ds . 'lib' . $ds . 'session.class.php';  
	require_once 'modules' . $ds . 'api' . $ds . 'lib' . $ds . 'registry.class.php'; 
	require_once 'modules' . $ds . 'api' . $ds . 'lib' . $ds . 'controller.class.php'; 
	require_once 'modules' . $ds . 'api' . $ds . 'lib' . $ds . 'applicationhelper.class.php'; 
	require_once 'modules' . $ds . 'api' . $ds . 'lib' . $ds . 'templatehelper.class.php'; 
	require_once 'modules' . $ds . 'api' . $ds . 'lib' . $ds . 'profiler.class.php'; 
	require_once 'modules' . $ds . 'api' . $ds . 'lib' . $ds . 'imagefile.class.php'; 
	require_once 'modules' . $ds . 'api' . $ds . 'lib' . $ds . 'resthelper.class.php'; 
	require_once 'modules' . $ds . 'thirdparty' . $ds . 'patForms' . $ds . 'patForms' . $ds . 'Datasource' . $ds . 'Propel.php'; 

	if (PF_CMS_ENABLED == true) {	
		require_once 'modules' . $ds . 'api' . $ds . 'lib' . $ds . 'cmshelper.class.php'; 
		//include('modules' . $ds . 'thirdparty' . $ds . 'spaw2' . $ds . 'spaw.inc.php');
	}

	if (file_exists(PF_BASE . $ds . 'modules' . $ds . 'shop' . $ds . 'lib' . $ds . 'cart.class.php')) {
		require_once 'modules' . $ds . 'shop' . $ds . 'lib' . $ds . 'cart.class.php';
	}

	// load PEAR path
	if (PF_USE_LOCAL_PEAR == true) {
		$pf_inc = PF_BASE . $ds . 'modules' . $ds . 'thirdparty' . $ds . 'pear' . $ps;
	} else {
		$pf_inc = PF_PEAR_BASE . $ps;
	}

	// add any additional third-party library include paths below
	$pf_inc .= PF_BASE . $ds . 'modules' . $ds . 'thirdparty' . $ds . 'smarty' . $ps;
	$pf_inc .= PF_BASE . $ds . 'modules' . $ds . 'thirdparty' . $ds . 'fpdf' . $ps;
	$pf_inc .= PF_BASE . $ds . 'modules' . $ds . 'thirdparty' . $ds . 'patError' . $ps;
	$pf_inc .= PF_BASE . $ds . 'modules' . $ds . 'thirdparty' . $ds . 'patForms' . $ps;
	$pf_inc .= PF_BASE . $ds . 'modules' . $ds . 'thirdparty' . $ds . 'patForms' . $ds . 'patForms' . $ps;
	$pf_inc .= PF_BASE . $ds . 'modules' . $ds . 'db' . $ps;
	ini_set('include_path', $pf_inc . $ps . ini_get('include_path'));

	PFProfiler::getInstance()->setMark('Starting Propel Loads');
	try {
		require_once 'propel' . $ds . 'Propel.php';
		require_once 'propel' . $ds . 'om' . $ds . 'BaseObject.php';

		if (file_exists(PF_BASE . $ds . 'modules' . $ds . 'db' . $ds . 'conf' . $ds . 'protean-conf.php')) {
			Propel::init(PF_BASE . $ds . 'modules' . $ds . 'db' . $ds . 'conf' . $ds . 'protean-conf.php');
		} else {
			throw new PFException('', 'Propel failed to load. Config file not found.', E_USER_ERROR);
		}

		// handle full query logging if this constant is set true
		if (PF_QUERY_DEBUG) {
			$config = Propel::getConfiguration(PropelConfiguration::TYPE_OBJECT);
			$config->setParameter('debugpdo.logging.details.method.enabled', true);
			$config->setParameter('debugpdo.logging.details.time.enabled', true);
			$config->setParameter('debugpdo.logging.details.mem.enabled', true);
		}

	} catch (PFException $e) {
		$e->handleException();
	} catch (Exception $e) {
		PFException::handleVanillaException($e->getMessage(), $e->getCode(), $e->getFile(), $e->getLine());
	} 

	//** Memcache Support
	if (PF_CACHE_ENABLED) {

		try {

			PFFactory::getInstance()->initObject('api.cachememcache');

			// Add Memcache servers here
			if (PF_CACHE_MEMCACHE_SERVER_HOST_1 != false)
				PFCacheMemcache::getInstance()->addServer(PF_CACHE_MEMCACHE_SERVER_HOST_1);

			if (PF_CACHE_MEMCACHE_SERVER_HOST_2 != false)
				PFCacheMemcache::getInstance()->addServer(PF_CACHE_MEMCACHE_SERVER_HOST_2);

			if (PF_CACHE_MEMCACHE_SERVER_HOST_3 != false)
				PFCacheMemcache::getInstance()->addServer(PF_CACHE_MEMCACHE_SERVER_HOST_3);

			if (PF_CACHE_MEMCACHE_SERVER_HOST_4 != false)
				PFCacheMemcache::getInstance()->addServer(PF_CACHE_MEMCACHE_SERVER_HOST_4);

		} catch (PFException $e) {
			$e->HandleException();
		} catch (Exception $e) {
			PFException::HandleVanillaException($e->getMessage(), $e->getCode(), $e->getFile(), $e->getLine());
		} 
	}


	//** Multilanguage Support 
	try {

		PFLanguage::GetInstance();
		$request = PFFactory::GetInstance()->CreateObject('api.request');

		$lang = $request->GetProperty('lang');
		$app = $request->GetProperty('app');

		if (isset($lang)) {
			PFLanguage::GetInstance()->SetCurrentLocale($lang);
		}

		if (!isset($app)) {
			$app = 'content';
		}
	} catch (PFException $e) {
		$e->HandleException();
	}

	try {

		// load default API global language table for error messages
		PFLanguage::GetInstance()->LoadTranslationTable('api', 'global');

		// load default static global language table for navigation/content messages
		//PFLanguage::GetInstance()->LoadTranslationTable('content', 'global');

		// load other application's language tables
		PFLanguage::GetInstance()->LoadTranslationTable($app, 'global');
	} catch (PFException $e) {
		$e->HandleException();
	}

	// if we're profiling, let's initialize it here.
	if (PF_PROFILER) {
		require_once 'modules' . $ds . 'api' . $ds . 'lib' . $ds . 'profiler.class.php';
		PFProfiler::GetInstance()->SetStartTime($startTime);
		PFProfiler::GetInstance()->SaveMarks(true);
	}

	function printr($arr, $buffered=false) {

		if (php_sapi_name() != 'cli') {
			echo '<pre>';
			$newLine = '<br />';
		} else {
			$newLine = "\n";
		}
		if (!empty($arr)) {
			print_r($arr, $buffered);
			echo $newLine;
		}
		if (php_sapi_name() != 'cli') {
			echo '</pre>';
		}
	}

	function printrBuffered($arr) {
		return (printr($arr, true));
	}

	?>
