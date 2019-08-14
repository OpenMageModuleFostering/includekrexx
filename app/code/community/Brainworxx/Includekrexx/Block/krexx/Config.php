<?php
/**
 * @file
 * Configfunctions for kreXX
 * kreXX: Krumo eXXtended
 *
 * This is a debugging tool, which displays structured information
 * about any PHP object. It is a nice replacement for print_r() or var_dump()
 * which are used by a lot of PHP developers.
 * @author brainworXX GmbH <info@brainworxx.de>
 *
 * kreXX is a fork of Krumo, which was originally written by:
 * Kaloyan K. Tsvetkov <kaloyan@kaloyan.info>
 *
 * @license http://opensource.org/licenses/LGPL-2.1 GNU Lesser General Public License Version 2.1
 * @package Krexx
 */

namespace Krexx;

/**
 * This class hosts the kreXX configuration functions.
 *
 * @package Krexx
 */
class Config {

  /**
   * Stores if kreXX is actually enabled.
   *
   * @var bool
   */
  protected static $isEnabled = TRUE;

  /**
   * Fallback settings, in case there is nothing in the config ini.
   *
   * @var array
   */
  public static $configFallback = array(
    'render' => array(
      'skin' => 'hans',
      'jsLib' => 'jquery-1.11.0.js',
      'memoryLeft' => '64',
      'maxRuntime' => '60',
    ),
    'logging' => array(
      'folder' => 'log',
      'maxfiles' => '10',
    ),
    'output' => array(
      'destination' => 'frontend',
      'useCookies' => 'false',
      'maxCall' => '10',
      'disabled' => 'false',
      'detectAjax' => 'true',
    ),
    'deep' => array(
      'analyseProtected' => 'false',
      'analysePrivate' => 'false',
      'analyseTraversable' => 'true',
      'debugMethods' => 'debug,__toArray,toArray,__toString,toString,_getProperties',
      'level' => '5',
    ),
    'methods' => array(
      'analysePublicMethods' => 'true',
      'analyseProtectedMethods' => 'false',
      'analysePrivateMethods' => 'false',
    ),
    'errorHandling' => array(
      'registerAutomatically' => 'false',
      'backtraceAnalysis' => 'normal',
    ),
  );

  protected static $feConfigFallback = array(
    'analysePublicMethods' => array(
      'type' => 'Select',
      'editable' => 'true',
    ),
    'analyseProtectedMethods' => array(
      'type' => 'Select',
      'editable' => 'true',
    ),
    'analysePrivateMethods' => array(
      'type' => 'Select',
      'editable' => 'true',
    ),
    'analyseProtected' => array(
      'type' => 'Select',
      'editable' => 'true',
    ),
    'analysePrivate' => array(
      'type' => 'Select',
      'editable' => 'true',
    ),
    'analyseTraversable' => array(
      'type' => 'Select',
      'editable' => 'true',
    ),
    'debugMethods' => array(
      'type' => 'Input',
      'editable' => 'false',
    ),
    'level' => array(
      'type' => 'Input',
      'editable' => 'true',
    ),
    'maxCall' => array(
      'type' => 'Input',
      'editable' => 'true',
    ),
    'disabled' => array(
      'type' => 'Select',
      'editable' => 'true',
    ),
    'useCookies' => array(
      'type' => 'Select',
      'editable' => 'true',
    ),
    'destination' => array(
      'type' => 'Select',
      'editable' => 'true',
    ),
    'maxfiles' => array(
      'type' => 'None',
      'editable' => 'false',
    ),
    'folder' => array(
      'type' => 'None',
      'editable' => 'false',
    ),
    'jsLib' => array(
      'type' => 'None',
      'editable' => 'false',
    ),
    'skin' => array(
      'type' => 'Select',
      'editable' => 'true',
    ),
    'registerAutomatically' => array(
      'type' => 'Select',
      'editable' => 'true',
    ),
    'detectAjax' => array(
      'type' => 'Select',
      'editable' => 'true',
    ),
    'backtraceAnalysis' => array(
      'type' => 'Select',
      'editable' => 'true',
    ),
    'memoryLeft' => array(
      'type' => 'Input',
      'editable' => 'true',
    ),
    'maxRuntime' => array(
      'type' => 'Input',
      'editable' => 'true',
    ),
  );

  /**
   * Caching for the local settings.
   *
   * @var array
   */
  protected static $localConfig = array();

  /**
   * Path to the configuration file.
   *
   * @var string
   */
  protected static $pathToIni;

  /**
   * Get\Set kreXX state: whether it is enabled or disabled.
   *
   * @param bool $state
   *   Optional, to enable or disable kreXX manually.
   * @param bool $ignore_local_settings
   *   Are we ignoring local cookie settings? Should only be
   *   TRUE when we render the settings menu only.
   *
   * @return bool
   *   Returns wether kreXX is enabled or not.
   */
  public Static Function isEnabled($state = NULL, $ignore_local_settings = FALSE) {

    // Enable kreXX.
    if (isset($state)) {
      self::$isEnabled = $state;
      return self::$isEnabled;
    }

    // Disabled in the ini or in the local settings?
    if (Config::getConfigValue('output', 'disabled', $ignore_local_settings) == 'true') {
      // self::$isEnabled = FALSE;
      return FALSE;
    }

    // Are we using Debug cookies?
    if (Config::getConfigValue('output', 'useCookies', $ignore_local_settings) == 'true') {
      if (isset($_COOKIE['KrexxDebug']) && $_COOKIE['KrexxDebug'] == 'yes') {
        $debug_session = TRUE;
      }
      else {
        $debug_session = FALSE;
      }

      if ($debug_session === TRUE && self::$isEnabled === TRUE) {
        return TRUE;
      }
      else {
        return FALSE;
      }
    }

    return self::$isEnabled;
  }

  /**
   * Returns values from kreXX's configuration.
   *
   * @param string $group
   *   The goup inside the ini of the value that we want to read.
   * @param string $name
   *   The name of the config value.
   * @param bool $ignore_local_settings
   *   Are we ignoring local settings.
   *
   * @return string
   *   The value.
   */
  public Static Function getConfigValue($group, $name, $ignore_local_settings = FALSE) {
    // Do some caching.
    // When we ignore the local settings, we can not rely on the cached value.
    if (isset(self::$localConfig[$group][$name]) && $ignore_local_settings == FALSE) {
      return self::$localConfig[$group][$name];
    }

    // Do we have a value in the cookies?
    if ($ignore_local_settings == FALSE) {

      $local_setting = self::getConfigFromCookies($group, $name);
      if (isset($local_setting)) {
        // We must not overwrite a disabled=true with local cookie settings!
        // Otherwise it couldget enabled locally,
        // which might be a security issue.
        if ($name == 'disabled' && $local_setting == 'false') {
          // Do nothing.
          // We ignore this setting.
        }
        else {
          self::$localConfig[$group][$name] = $local_setting;
          return $local_setting;
        }
      }
    }

    // Do we have a value in the ini?
    $ini_settings = self::getConfigFromFile($group, $name);
    if (isset($ini_settings)) {
      self::$localConfig[$group][$name] = $ini_settings;
      return $ini_settings;
    }

    // Nothing yet? Give back factory settings.
    self::$localConfig[$group][$name] = self::$configFallback[$group][$name];
    return self::$configFallback[$group][$name];
  }

  /**
   * Here we overwrite the local settings.
   *
   * When we are handling errors and are analysing objects, we should
   * output protected and private variables of a class, outputting as
   * much info as possible.
   *
   * @param array $new_settings
   *   Part of the array we want to overwrite.
   *
   * @todo In case of a fatal error handler, it is not probemlatical to simply
   *   overwrite these values. Later on, we might want to restore the
   *   old values.
   */
  public static function overwriteLocalSettings(array $new_settings) {
    self::krexxArrayMerge(self::$localConfig, $new_settings);
  }

  /**
   * We merge recursively two arrays.
   *
   * We keep the keys and overwrite the original values
   * of the $old_array.
   *
   * @param array $old_array
   *   The array we want to change.
   * @param array $new_array
   *   The new values for the $old_array.
   */
  protected static function krexxArrayMerge(array &$old_array, array &$new_array) {
    foreach ($new_array as $key => $value) {
      if (!isset($old_array[$key])) {
        // We simply add it.
        $old_array[$key] = $value;
      }
      else {
        // We have already a value.
        if (is_array($value)) {
          // Add our array rekursivly.
          self::krexxArrayMerge($old_array[$key], $value);
        }
        else {
          // It's not an array, we simply overwrite the value.
          $old_array[$key] = $value;
        }
      }
    }
  }

  /**
   * Returns the whole configuration as an array.
   *
   * The source of the value (factory, ini or cookie)
   * is also included. We need this one for the display
   * on the frontend.
   * We display here the invalid settings (if we have
   * any,so the user can corrent it.
   *
   * @return array
   *   The configuration with the source.
   */
  public static function getWholeConfiguration() {
    // We may have some project settings in the ini
    // as well as some in the cookies, but some may be missing.
    $source = array();
    $config = array();
    $cookie_config = array();

    // Get Settings from the cookies. We do not valuate them,
    // so the dev can correct them, in case there are wrong values.
    if (isset($_COOKIE['KrexxDebugSettings'])) {
      $cookie_config = json_decode($_COOKIE['KrexxDebugSettings'], TRUE);
    }

    // Get Settings from the cookies. We do not valuate them,
    // so the dev can correct them, in case there are wrong values.
    if (is_readable(self::$pathToIni)) {
      $config_ini = (array) parse_ini_file(self::$pathToIni, TRUE);
    }

    foreach (self::$configFallback as $section_name => $section_data) {
      foreach ($section_data as $parameter_name => $parameter_value) {
        // Get cookie settings.
        if (isset($cookie_config[$parameter_name])) {
          $config[$section_name][$parameter_name] = htmlspecialchars($cookie_config[$parameter_name]);
          $source[$section_name][$parameter_name] = 'local cookie settings';
        }

        else {
          // File settings.
          if (isset($config_ini[$section_name][$parameter_name])) {
            $config[$section_name][$parameter_name] = htmlspecialchars($config_ini[$section_name][$parameter_name]);
            $source[$section_name][$parameter_name] = 'Krexx ini settings';
            continue;
          }
          else {
            // Nothing yet? Return factory settings.
            $config[$section_name][$parameter_name] = $parameter_value;
            $source[$section_name][$parameter_name] = 'factory settings';
          }
        }
      }
    }
    $result = array(
      $source,
      $config,
    );
    return $result;
  }

  /**
   * Returns the developer handle from the cookies.
   *
   * @return string
   *   The Developer handle.
   */
  public static function getDevHandler() {
    return self::getConfigFromCookies('deep', 'Local open function');
  }

  /**
   * Gets the path to the inifile.
   *
   * In typo3, it is not a good idea to store the config
   * settings inside the module directory. When an update is
   * triggered, all settings will be lost. So wen need a functionality
   * to point kreXX to another directory for it's config.
   *
   * @return string
   *   The path to the inifile
   */
  public Static Function getPathToIni() {
    return self::$pathToIni;
  }

  /**
   * Returns settings from the ini file.
   *
   * @param string $group
   *   The group name inside of the ini.
   * @param string $name
   *   The name of the setting.
   *
   * @return string
   *   The value from the file.
   */
  public static function getConfigFromFile($group, $name) {
    static $_config = array();

    // Not loaded?
    if (empty($_config)) {
      // File is somewhere else.
      if (is_readable(KREXXDIR . 'KrexxConfig.ini')) {
        $config_ini = (array) parse_ini_file(KREXXDIR . 'KrexxConfig.ini', TRUE);
      }
      if (isset($config_ini['pathtoini']['pathtoini'])) {
        self::$pathToIni = $config_ini['pathtoini']['pathtoini'];
      }
      else {
        self::$pathToIni = KREXXDIR . 'Krexx.ini';
      }

      if (is_readable(self::$pathToIni)) {
        $_config = (array) @parse_ini_file(self::$pathToIni, TRUE);
      }
    }

    // Do we have a value in the ini?
    if (isset($_config[$group][$name]) && self::evaluateSetting($_config[$group][$name], $name)) {
      return $_config[$group][$name];
    }
  }

  /**
   * Returns settings from the local cookies.
   *
   * @param string $group
   *   The name of the group inside the cookie.
   * @param string $name
   *   The name of the value.
   *
   * @return mixed
   *   The value.
   */
  public static function getConfigFromCookies($group, $name) {
    static $_config = array();

    if (!isset($_COOKIE['KrexxDebugSettings'])) {
      // No cookies, no result.
      return;
    }

    // Not loaded?
    if (empty($_config)) {
      // We have local settings.
      $_config = json_decode($_COOKIE['KrexxDebugSettings'], TRUE);
    }

    $param_config = self::getFrontendConfigConfiguration($name);
    if ($param_config[0] === FALSE) {
      // We act as if we have not found the value. Configurations that are
      // not editable on the frontend will be ignored!
      return;
    }
    // Do we have a value in the cookies?
    if (isset($_config[$name]) && self::evaluateSetting($_config[$name], $name)) {
      // We escape them, just in case.
      $value = htmlspecialchars($_config[$name]);

      return $value;
    }
  }

  /**
   * Evaluate a single setting from the cookies or the ini file.
   *
   * @param mixed $value
   *   The value to evaluate.
   * @param string $name
   *   The name of the setting.
   *
   * @return bool
   *   If it was evaluated.
   */
  public static function evaluateSetting($value, $name) {
    static $evaluated = array();

    if (!isset($evaluated[$name])) {
      // We must evaluate it.
      $result = FALSE;
      switch ($name) {
        case "analysePublicMethods":
          // We expect a bool.
          $result = self::evalBool($value);
          if (!$result) {
            Messages::addMessage('Wrong configuration for: "methods => analysePublicMethods"! Expected boolean. The configured setting was not applied!');
          }
          break;

        case "analyseProtectedMethods":
          // We expect a bool.
          $result = self::evalBool($value);
          if (!$result) {
            Messages::addMessage('Wrong configuration for: "methods => analyseProtectedMethods"! Expected boolean. The configured setting was not applied!');
          }
          break;

        case "analysePrivateMethods":
          // We expect a bool.
          $result = self::evalBool($value);
          if (!$result) {
            Messages::addMessage('Wrong configuration for: "methods => analysePrivateMethods"! Expected boolean. The configured setting was not applied!');
          }
          break;

        case "analyseProtected":
          // We expect a bool.
          $result = self::evalBool($value);
          if (!$result) {
            Messages::addMessage('Wrong configuration for: "deep => analyseProtected"! Expected boolean. The configured setting was not applied!');
          }
          break;

        case "analysePrivate":
          // We expect a bool.
          $result = self::evalBool($value);
          if (!$result) {
            Messages::addMessage('Wrong configuration for: "deep => analysePrivate"! Expected boolean. The configured setting was not applied!');
          }
          break;

        case "analyseTraversable":
          // We expect a bool.
          $result = self::evalBool($value);
          if (!$result) {
            Messages::addMessage('Wrong configuration for: "deep => analyseTraversable"! Expected boolean. The configured setting was not applied!');
          }
          break;

        case "debugMethods":
          // String that can get exploded, separated by a comma,
          // might as well be a single function.
          // We are not going to check this one.
          $result = TRUE;
          break;

        case "level":
          // We expect an integer.
          $result = self::evalInt($value);
          if (!$result) {
            Messages::addMessage('Wrong configuration for: "deep => level"! Expected integer. The configured setting was not applied!');
          }
          break;

        case "maxCall":
          // We expect an integer.
          $result = self::evalInt($value);
          if (!$result) {
            Messages::addMessage('Wrong configuration for: "output => maxCall"! Expected integer. The configured setting was not applied!');
          }
          break;

        case "disabled":
          // We expect a bool.
          $result = self::evalBool($value);
          if (!$result) {
            Messages::addMessage('Wrong configuration for: "output => disabled"! Expected boolean. The configured setting was not applied!');
          }
          break;

        case "detectAjax":
          // We expect a bool.
          $result = self::evalBool($value);
          if (!$result) {
            Messages::addMessage('Wrong configuration for: "output => detectAjax"! Expected boolean. The configured setting was not applied!');
          }
          break;

        case "useCookies":
          // We expect a bool.
          $result = self::evalBool($value);
          if (!$result) {
            Messages::addMessage('Wrong configuration for: "output => useCookies"! Expected boolean. The configured setting was not applied!');
          }
          break;

        case "destination":
          // We expect 'frontend' or 'file'
          if ($value == 'frontend' || $value == 'file') {
            $result = TRUE;
          }
          if (!$result) {
            Messages::addMessage('Wrong configuration for: "output => destination"! Expected "frontend" or "file". The configured setting was not applied!');
          }
          break;

        case "maxfiles":
          // We expect an integer.
          $result = self::evalInt($value);
          if (!$result) {
            Messages::addMessage('Wrong configuration for: "output => maxfiles"! Expected integer. The configured setting was not applied!');
          }
          break;

        case "folder":
          // Directory with writeaccess.
          // We also need to check, if the folder is properly protected.
          $is_writable = is_writable(KREXXDIR . $value);
          $is_protected = Toolbox::isFolderProtected(KREXXDIR . $value);
          if ($is_writable && $is_protected) {
            $result = TRUE;
          }
          if (!$is_writable) {
            Messages::addMessage('Wrong configuration for: "output => folder"! Directory is not writeable. The configured setting was not applied!');
          }
          if (!$is_protected) {
            Messages::addMessage('Wrong configuration for: "output => folder"! Directory is not protected. The configured setting was not applied!');
          }
          break;

        case "jsLib":
          // We expect a path to a jquery library, or an empty value.
          if (is_readable(KREXXDIR . 'jsLibs/' . $value)) {
            // Check if this is jquery.
            $file_contents = file_get_contents(KREXXDIR . 'jsLibs/' . $value);
            $is_jquery = strpos($file_contents, 'jQuery Foundation, Inc.') !== FALSE;
          }
          // We accept empty values and jquery libraries.
          if (empty($value) || $is_jquery) {
            $result = TRUE;
          }
          else {
            Messages::addMessage('Wrong configuration for: "render => jsLib"! This is not a jQuery library. The configured setting was not applied!');
          }
          break;

        case "doctype":
          // We expect a string, could be anything.
          $result = TRUE;
          break;

        case "skin":
          // We check the directory and one of the files for readability.
          if (is_dir(KREXXDIR . 'skins/' . $value) && is_readable(KREXXDIR . 'skins/' . $value . '/header.html')) {
            $result = TRUE;
          }
          if (!$result) {
            Messages::addMessage('Wrong configuration for: "render => skin"! Skin not found. The configured setting was not applied!');
          }
          break;

        case "Local open function":
          // The Developer handle,
          // we are not going to check this one, could be anything you can type.
          $result = TRUE;
          break;

        case "traceFatals":
          // We expect a bool.
          $result = self::evalBool($value);
          if (!$result) {
            Messages::addMessage('Wrong configuration for: "errorHandling => traceFatals"! Expected boolean. The configured setting was not applied!');
          }
          break;

        case "traceWarnings":
          // We expect a bool.
          $result = self::evalBool($value);
          if (!$result) {
            Messages::addMessage('Wrong configuration for: "errorHandling => traceWarnings"! Expected boolean. The configured setting was not applied!');
          }
          break;

        case "traceNotices":
          // We expect a bool.
          $result = self::evalBool($value);
          if (!$result) {
            Messages::addMessage('Wrong configuration for: "errorHandling => traceNotices"! Expected boolean. The configured setting was not applied!');
          }
          break;

        case "registerAutomatically":
          // We expect a bool.
          $result = self::evalBool($value);
          if (!$result) {
            Messages::addMessage('Wrong configuration for: "errorHandling => registerAutomatically"! Expected boolean. The configured setting was not applied!');
          }
          break;

        case "backtraceAnalysis":
          // We expect "normal" or "deep"
          if ($value == 'normal' || $value == 'deep') {
            $result = TRUE;
          }
          if (!$result) {
            Messages::addMessage('Wrong configuration for: "errorHandling => backtraceAnalysis"! Expected "normal" or "deep". The configured setting was not applied!');
          }
          break;

        case "memoryLeft";
          // We expect an integer.
          $result = self::evalInt($value);
          if (!$result) {
            Messages::addMessage('Wrong configuration for: "render => memoryLeft"! Expected integer. The configured setting was not applied!');
          }
          break;

        case "maxRuntime":
          // We expect an integer.
          $result = self::evalInt($value);
          if (!$result) {
            Messages::addMessage('Wrong configuration for: "render => maxRuntime"! Expected integer. The configured setting was not applied!');
          }
          break;

        default:
          // Unknown settings,
          // return false, just in case.
          break;
      }
      $evaluated[$name] = $result;
    }
    return $evaluated[$name];
  }

  /**
   * Get the configuration of the frontend config form.
   *
   * @param string $parameter_name
   *   The parameter you want to render.
   *
   * @return array
   *   The configuration (is it editable, a dropdown, a textfield, ...)
   */
  public static function getFrontendConfigConfiguration($parameter_name) {
    static $config = array();

    // Not loaded?
    if (empty($config)) {
      // File is somewhere else.
      if (is_readable(KREXXDIR . 'KrexxConfig.ini')) {
        $config_ini = (array) parse_ini_file(KREXXDIR . 'KrexxConfig.ini', TRUE);
      }
      if (isset($config_ini['pathtoini']['pathtofeconfig'])) {
        $path = $config_ini['pathtoini']['pathtoini'];
      }
      else {
        $path = KREXXDIR . 'FeConfig.ini';
      }
      if (is_readable(KREXXDIR . 'FeConfig.ini')) {
        $config = (array) parse_ini_file(KREXXDIR . 'FeConfig.ini', TRUE);
      }
    }

    // Trying to get the value.
    if (isset($config[$parameter_name])) {
      $type = $config[$parameter_name]['type'];
      $editable = $config[$parameter_name]['editable'];
    }
    else {
      // Fallback to factory settings.
      if (isset(self::$feConfigFallback[$parameter_name])) {
        $type = self::$feConfigFallback[$parameter_name]['type'];
        $editable = self::$feConfigFallback[$parameter_name]['editable'];
      }
      else {
        // Unknown parameter.
        $type = 'None';
        $editable = 'false';
      }
    }
    if ($editable === 'true') {
      $editable = TRUE;
    }
    else {
      $editable = FALSE;
    }

    return array($editable, $type);
  }

  /**
   * Evaluates a string of 'true' or 'false'.
   *
   * @param string $value
   *   The string we want to evaluate.
   *
   * @return bool
   *   Weather it does evaluate or not.
   */
  protected static function evalBool($value) {
    if ($value === 'true' || $value === 'false') {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

  /**
   * Evaluates a string of integer.
   *
   * It must be greater than 0 and smaller than 101.
   *
   * @param string $value
   *   The string we want to evaluate.
   *
   * @return bool
   *   Weather it does evaluate or not.
   */
  protected static function evalInt($value) {
    $value = (int) $value;
    if ($value > 0) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }
}
