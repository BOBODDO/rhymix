<?php

/**
 * Skip if Rhymix is already loaded.
 */
if (defined('RX_VERSION'))
{
	return;
}

/**
 * Set error reporting rules.
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);

/**
 * Suppress date/time errors until the internal time zone is set (see below).
 */
date_default_timezone_set(@date_default_timezone_get());

/**
 * Set the default character encoding.
 */
ini_set('default_charset', 'UTF-8');
if (function_exists('mb_internal_encoding'))
{
	mb_internal_encoding('UTF-8');
}
if (function_exists('mb_regex_encoding'))
{
	mb_regex_encoding('UTF-8');
}

/**
 * Load constants and common functions.
 */
require_once __DIR__ . '/constants.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/legacy.php';

/**
 * Define the list of legacy class names for the autoloader.
 */
$GLOBALS['RX_AUTOLOAD_FILE_MAP'] = array_change_key_case(array(
	'CacheHandler' => 'classes/cache/CacheHandler.class.php',
	'Context' => 'classes/context/Context.class.php',
	'DB' => 'classes/db/DB.class.php',
	'DisplayHandler' => 'classes/display/DisplayHandler.class.php',
	'HTMLDisplayHandler' => 'classes/display/HTMLDisplayHandler.php',
	'JSCallbackDisplayHandler' => 'classes/display/JSCallbackDisplayHandler.php',
	'JSONDisplayHandler' => 'classes/display/JSONDisplayHandler.php',
	'RawDisplayHandler' => 'classes/display/RawDisplayHandler.php',
	'VirtualXMLDisplayHandler' => 'classes/display/VirtualXMLDisplayHandler.php',
	'XMLDisplayHandler' => 'classes/display/XMLDisplayHandler.php',
	'EditorHandler' => 'classes/editor/EditorHandler.class.php',
	'ExtraVar' => 'classes/extravar/Extravar.class.php',
	'ExtraItem' => 'classes/extravar/Extravar.class.php',
	'FileHandler' => 'classes/file/FileHandler.class.php',
	'FileObject' => 'classes/file/FileObject.class.php',
	'FrontEndFileHandler' => 'classes/frontendfile/FrontEndFileHandler.class.php',
	'Handler' => 'classes/handler/Handler.class.php',
	'XEHttpRequest' => 'classes/httprequest/XEHttpRequest.class.php',
	'Mail' => 'classes/mail/Mail.class.php',
	'Mobile' => 'classes/mobile/Mobile.class.php',
	'ModuleHandler' => 'classes/module/ModuleHandler.class.php',
	'ModuleObject' => 'classes/module/ModuleObject.class.php',
	'PageHandler' => 'classes/page/PageHandler.class.php',
	'EmbedFilter' => 'classes/security/EmbedFilter.class.php',
	'IpFilter' => 'classes/security/IpFilter.class.php',
	'Password' => 'classes/security/Password.class.php',
	'Purifier' => 'classes/security/Purifier.class.php',
	'Security' => 'classes/security/Security.class.php',
	'UploadFileFilter' => 'classes/security/UploadFileFilter.class.php',
	'TemplateHandler' => 'classes/template/TemplateHandler.class.php',
	'Validator' => 'classes/validator/Validator.class.php',
	'WidgetHandler' => 'classes/widget/WidgetHandler.class.php',
	'GeneralXmlParser' => 'classes/xml/GeneralXmlParser.class.php',
	'Xml_Node_' => 'classes/xml/XmlParser.class.php',
	'XmlGenerator' => 'classes/xml/XmlGenerator.class.php',
	'XmlJsFilter' => 'classes/xml/XmlJsFilter.class.php',
	'XmlLangParser' => 'classes/xml/XmlLangParser.class.php',
	'XmlParser' => 'classes/xml/XmlParser.class.php',
	'XeXmlParser' => 'classes/xml/XmlParser.class.php',
	'Ftp' => 'common/libraries/ftp.php',
	'Tar' => 'common/libraries/tar.php',
	'CryptoCompat' => 'common/libraries/cryptocompat.php',
	'VendorPass' => 'common/libraries/vendorpass.php',
), CASE_LOWER);

/**
 * Define the autoloader.
 */
spl_autoload_register(function($class_name)
{
	$filename = false;
	$langpath = false;
	$lc_class_name = str_replace('\\', '/', strtolower($class_name));
	if (preg_match('!^rhymix/(framework|addons|modules|plugins)/(.+)$!', $lc_class_name, $matches))
	{
		$filename = RX_BASEDIR . ($matches[1] === 'framework' ? 'common/framework' : $matches[1]) . '/' . $matches[2] . '.php';
		if ($matches[1] !== 'framework')
		{
			$langpath = RX_BASEDIR . $matches[1] . '/lang';
		}
	}
	elseif (isset($GLOBALS['RX_AUTOLOAD_FILE_MAP'][$lc_class_name]))
	{
		$filename = RX_BASEDIR . $GLOBALS['RX_AUTOLOAD_FILE_MAP'][$lc_class_name];
	}
	elseif (preg_match('/^([a-zA-Z0-9_]+?)(Admin)?(View|Controller|Model|Item|Api|Wap|Mobile)?$/', $class_name, $matches))
	{
		$module = strtolower($matches[1]);
		$filename = RX_BASEDIR . 'modules/' . $module . '/' . $module .
			(!empty($matches[2]) ? '.admin' : '') .
			(!empty($matches[3]) ? ('.' . strtolower($matches[3])) : '.class') . '.php';
		if ($module !== 'module')
		{
			$langpath = RX_BASEDIR . 'modules/' . $module . '/lang';
		}
	}
	if ($filename && file_exists($filename))
	{
		include $filename;
		if ($langpath)
		{
			Context::loadLang($langpath);
		}
	}
});

/**
 * Also include the Composer autoloader.
 */
require_once RX_BASEDIR . 'vendor/autoload.php';

/**
 * Load essential classes.
 */
require_once RX_BASEDIR . 'classes/object/Object.class.php';

/**
 * Load user configuration.
 */
if(file_exists(RX_BASEDIR . 'config/config.user.inc.php'))
{
	require_once RX_BASEDIR . 'config/config.user.inc.php';
}

/**
 * Load system configuration.
 */
Rhymix\Framework\Config::init();

/**
 * Install the debugger.
 */
Rhymix\Framework\Debug::registerErrorHandlers(error_reporting());

/**
 * Set the internal timezone.
 */
$internal_timezone = Rhymix\Framework\DateTime::getTimezoneNameByOffset(config('locale.internal_timezone'));
date_default_timezone_set($internal_timezone);

/**
 * Initialize the cache handler.
 */
Rhymix\Framework\Cache::init(Rhymix\Framework\Config::get('cache'));
