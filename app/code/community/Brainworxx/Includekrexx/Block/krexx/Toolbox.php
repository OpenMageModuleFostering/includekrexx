<?php
/**
 * @file
 * Toolbox functions for kreXX
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
 * This class hosts functions, which offer additional services.
 *
 * @package Krexx
 */
class Toolbox {

  /**
   * Writes the output of kreXX to a file and cleans up the output folder.
   *
   * @param string $output
   *   The generated markup, which will be written to a file.
   */
  public static function saveOutputToFile($output) {
    static $log_dir;
    if (is_null($log_dir)) {
      $log_dir = Config::getConfigValue('logging', 'folder') . DIRECTORY_SEPARATOR;
    }

    $log_list = glob(KREXXDIR . $log_dir . "*.Krexx.html");
    array_multisort(array_map('filemtime', $log_list), SORT_DESC, $log_list);
    $max_file_count = (int) Config::getConfigValue('logging', 'maxfiles');
    $count = 1;
    // Cleanup logfiles.
    foreach ($log_list as $file) {
      if (is_file($file) && $count >= $max_file_count) {
        unlink($file);
      }
      $count++;
    }

    $timestamp = self::fileStamp();
    // Now we need to write this into a file.
    $filename = KREXXDIR . $log_dir . DIRECTORY_SEPARATOR . $timestamp . '.Krexx.html';
    Chunks::saveDechunkedToFile($filename, $output);
    // include_once $filename;
    // file_put_contents($filename, $output, FILE_APPEND);
  }

  /**
   * Returns the microtime timestamp for fileoperations.
   *
   * Fileoperations are the logfiles and the chunck handling.
   *
   * @return string
   *   The timestamp itself.
   */
  public static function fileStamp() {
    static $timestamp = 0;
    if ($timestamp == 0) {
      $timestamp = explode(" ", microtime());
      $timestamp = $timestamp[1] . str_replace("0.", "", $timestamp[0]);
    }

    return $timestamp;
  }

  /**
   * Sends the output to the browser.
   *
   * The mainproblem here is:
   * When the request comes via ajax, there is a good
   * chance that we are outputting a json or xml. Sending
   * kreXX via echo will destroy them
   *
   * @param string $output
   *   The generated markup, which will be send to the browser.
   */
  public static function sendOutputToBrowser($output) {
    // Check, if we are dealing with an ajax request.
    if (self::isRequestAjax()) {
      // We are facing an AJAX request.
      // We will not interfere here.
      // @todo Give feedback that we have new logfiles.
      // @todo self::saveOutputToFile($output);
      // It's not a good idea to create a file without explicite
      // permission from the developer.
      // Do nothing.
    }
    else {
      Chunks::sendDechunkedToBrowser($output);
    }
  }

  /**
   * Outputs a string, either to the browser or file.
   *
   * Wrapper for sendOutputToBrowser() and saveOutputToFile()
   *
   * @param string $string
   *   The generated DOM so far, for the output.
   */
  public static function outputNow($string, $ignore_local_settings = FALSE) {
    if (Config::getConfigValue('output', 'destination', $ignore_local_settings) == 'file') {
      // Save it to a file.
      Toolbox::saveOutputToFile($string);
    }
    else {
      // Send it to the browser.
      Toolbox::sendOutputToBrowser($string);
    }
  }


  /**
   * Check if the current request is an AJAX request.
   *
   * @return bool
   *   TRUE when this is AJAX, FALSE if not
   */
  public static function isRequestAjax() {
    static $result;

    if (is_null($result)) {
      if (Config::getConfigValue('output', 'detectAjax') == 'false') {
        // We are not suppost to cae about ajax, so we return always a FALSE.
        $result = FALSE;
      }
      else {
        $result = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
      }
    }

    return $result;
  }

  /**
   * Simply outputs the Header of kreXX.
   *
   * @param string $headline
   *   The headline, displayed in the header.
   * @param bool $ignore_local_settings
   *   Are we ignoring local cookie settings? Should only be
   *   TRUE when we render the settings menu only.
   *
   * @return string
   *   The generated markup
   */
  public static function outputHeader($headline, $ignore_local_settings = FALSE) {
    static $doc_type = NULL;

    // Do we do an output as file?
    $output_as_file = (Config::getConfigValue('output', 'destination') == 'file');
    // When we have a normal fileoutput, and ignore the local settings,
    // it means we are currently rendering the frontend "Edit local settings"
    // mask but outputting the rest into a file.
    // We need to render the CSS/JS for the frontend, because we have dual
    // output (frontend and file).
    $dual_output = ($output_as_file && $ignore_local_settings);

    if (!isset($doc_type) || $dual_output == TRUE) {
      // Send doctype and css/js only once.
      $doc_type = '<!DOCTYPE html>';
      return Render::renderHeader($doc_type, $headline, self::outputCssAndJs());
    }
    else {
      return Render::renderHeader('', $headline, '');
    }
  }

  /**
   * Simply renders the footer and output current settings.
   *
   * @param string $caller
   *   Where was kreXX initially invoced from.
   * @param bool $is_expanded
   *   Are we rendering an expanded footer?
   *   TRUE when we render the settings menu only.
   *
   * @return string
   *   The generated markup.
   */
  public static function outputFooter($caller, $is_expanded = FALSE) {

    // Wrap an expandable around to save space.
    $anon_function = function ($params) {
      $config = $params[0];
      $source = $params[1];
      $config_output = '';
      foreach ($config as $section_name => $section_data) {
        $params_expandable = array(
          $section_data,
          $source[$section_name]);

        // Render a whole section.
        $anonfunction = function ($params) {
          // $section_name = $params[0];
          $section_data = $params[0];
          $source = $params[1];
          $section_output = '';
          foreach ($section_data as $parameter_name => $parameter_value) {
            // Render the single value.
            // We need to find out where the value comes from.
            $config = Config::getFrontendConfigConfiguration($parameter_name);
            $editable = $config[0];
            $type = $config[1];

            if ($type != 'None') {
              if ($editable) {
                $section_output .= Render::renderSingleEditableChild($parameter_name, htmlspecialchars($parameter_value), $source[$parameter_name], $type, $parameter_name);
              }
              else {
                $section_output .= Render::renderSingleChild($parameter_value, $parameter_name, htmlspecialchars($parameter_value), FALSE, $source[$parameter_name], '', $parameter_name);
              }
            }
          }
          return $section_output;
        };
        $config_output .= Render::renderExpandableChild($section_name, 'Config', $anonfunction, $params_expandable, '. . .');
      }
      // Render the dev-handle field.
      $config_output .= Render::renderSingleEditableChild('Local open function', Config::getDevHandler(), '\krexx::', 'Input', 'localFunction');
      // Render the reset-button which will delete the debug-cookie.
      $config_output .= Render::renderButton('resetbutton', 'Reset local settings', 'resetbutton');
      $config_output .= Render::renderButton('debugcookie', 'Toggle debug cookie', 'debugcookie');
      return $config_output;
    };

    // Now we need to stitch together the content of the ini file
    // as well as it's path.
    if (!is_readable(Config::getPathToIni())) {
      // Project settings are not accessible
      // tell the user, that we are using fallback sttings.
      $path = 'Krexx.ini not found, using factory settings';
      // $config = array();
    }
    else {
      $path = 'Current configuration';
    }

    $my_config = Config::getWholeConfiguration();
    $source = $my_config[0];
    $config = $my_config[1];

    $parameter = array($config, $source);

    $config_output = Render::renderExpandableChild($path, Config::getPathToIni(), $anon_function, $parameter, '', '', 'currentSettings', $is_expanded);
    return Render::renderFooter($caller, $config_output);
  }

  /**
   * Outputs the CSS and JS.
   *
   * @return string
   *   The generated markup.
   */
  public Static Function outputCssAndJs() {
    static $been_here = FALSE;

    if ($been_here) {
      // We only send JS and CSS once.
      return '';
    }
    // Get the css file.
    $_ = KREXXDIR . 'skins/' . Render::$skin . '/skin.css';
    $css = file_get_contents($_);
    // Remove whitespace.
    $css = preg_replace('/\s+/', ' ', $css);

    $js = '';
    // Adding JQuery.
    $js_lib = KREXXDIR . 'jsLibs/' . Config::getConfigValue('render', 'jsLib');
    if (file_exists($js_lib)) {
      // We are not going to minimize the 3'rd party library.
      $js .= implode(file($js_lib));
    }

    // Krexx.js is comes directly form the template.
    $template_js = KREXXDIR . 'skins/' . Render::$skin . '/krexx.js';
    if (file_exists($template_js)) {
      $js .= implode(file($template_js));
    }
    $been_here = TRUE;
    return Render::renderCssJs($css, $js);
  }

  /**
   * Generates a id for the DOM.
   *
   * This is used to jump from a recursion to the object analysis data.
   * The ID is the object hash as well as the kruXX call number, to avoit
   * collusions (even if they are unlikely).
   *
   * @param mixed $data
   *   The object from which we want the ID.
   *
   * @return string
   *   The generated id.
   */
  public static function generateDomIdFromObject($data) {
    if (is_object($data)) {
      return 'k' . Render::$KrexxCount . '_' . spl_object_hash($data);
    }
    else {
      // Do nothing.
    }
  }

  /**
   * Simply outputs a formatted var_dump and then dies.
   *
   * This is an internal debugging function, because it is
   * rather difficult to debug a debugger, when your tool of
   * choise is the debugger itself.
   *
   * @param mixed $data
   *   The data for the var_dump.
   */
  public static function formatedVarDump($data) {
    echo '<pre>';
    var_dump($data);
    die('</pre>');
  }

  /**
   * Checks for a .htaccess file with a 'deny from all' statement.
   *
   * @param string $path
   *   The path we want to check.
   *
   * @return bool
   *   Whether the path is protected.
   */
  public static function isFolderProtected($path) {
    $result = FALSE;
    if (file_exists($path . '/.htaccess')) {
      $content = file($path . '/.htaccess');
      foreach ($content as $line) {
        // We have what we are looking for, a
        // 'deny from all', not to be confuse with
        // a '# deny from all'.
        if (strtolower(trim($line)) == 'deny from all') {
          $result = TRUE;
          break;
        }
      }
    }
    return $result;
  }

  /**
   * Adds source sampels to a backtrace.
   *
   * @param array $backtrace
   *   The backtrace from debug_backtrace().
   *
   * @return array
   *   The backtrace with the source samples.
   */
  protected static function addSourcecodeToBacktrace(array $backtrace) {
    foreach ($backtrace as &$trace) {
      // The line number is 0-based, we need to a -1.
      $source = self::readSourcecode($trace['file'], $trace['line'] - 1, 3);
      // Add it only, if we have source code. Some internal functions do not
      // provide any (call_user_func for example).
      if (strlen(trim($source)) > 0) {
        $trace['sourcecode'] = $source;
      }
      else {
        $trace['sourcecode'] = 'No sourcecode available. Maybe this was an internal callback (call_user_func for example)?';
      }
    }

    return $backtrace;
  }

  /**
   * Reads sourcecode from files, in case a fatal error acurred.
   *
   * @param string $file
   *   Path to the file you want to read.
   * @param int $line_no
   *   The line number you want to read.
   * @param int $space_line
   *   How many lines before and after the line number.
   *
   * @return string
   *   The source code.
   */
  public static function readSourcecode($file, $line_no, $space_line) {
    $result = '';
    if (is_readable($file)) {
      // Load content and add it to the backtrace.
      $content_array = file($file);
      $from = $line_no - $space_line;
      $to = $line_no + $space_line;
      // Correct the value, in case we are exeeding the line numbers.
      if ($from < 0) {
        $from = 0;
      }
      if ($to > count($content_array)) {
        $to = count($content_array);
      }

      for ($current_line_no = $from; $current_line_no <= $to; $current_line_no++) {
        if (isset($content_array[$current_line_no])) {
          // We are ignoring empty lines.
          $line = preg_replace('/\s+/', '', $content_array[$current_line_no]);
          if (strlen($line) == 0) {
            // We will need to incease the $to.
            if ($to + 1 <= count($content_array)) {
              $to++;
            }
          }
          // Add it to the result.
          $real_line_no = $current_line_no + 1;
          if ($current_line_no == $line_no) {
            $result .= Render::renderBacktraceSourceLine('highlight', $real_line_no, \Krexx\Variables::encodeString($content_array[$current_line_no], TRUE));
          }
          else {
            $result .= Render::renderBacktraceSourceLine('source', $real_line_no, \Krexx\Variables::encodeString($content_array[$current_line_no], TRUE));
          }
        }
        else {
          // End of the file.
          break;
        }
      }
    }
    return $result;
  }

  /**
   * Outputs a backtrace.
   *
   * We need to format this one a little bit different than a
   * normal array.
   *
   * @param array $backtrace
   *   The backtrace.
   *
   * @return string
   *   The rendered backtrace.
   */
  public static function outputBacktrace(array $backtrace) {
    $output = '';

    // Add the sourcecode to our backtrace.
    $backtrace = self::addSourcecodeToBacktrace($backtrace);

    foreach ($backtrace as $step => $step_data) {
      $name = $step;
      $type = 'Stack Frame';
      $parameter = $step_data;
      $anon_function = function($parameter){
        // We are handeling the following values here:
        // file, line, function, object, type, args, sourcecode.
        $step_data = $parameter;
        // File.
        if (isset($step_data['file'])) {
          $output = \Krexx\Render::renderSingleChild($step_data['file'], 'File', $step_data['file'], FALSE, 'string ', strlen($step_data['file']));
        }
        // Line.
        if (isset($step_data['line'])) {
          $output .= \Krexx\Render::renderSingleChild($step_data['line'], 'Line no.', $step_data['line'], FALSE, 'integer');
        }
        // Sourcecode, is escaped by now.
        if (isset($step_data['sourcecode'])) {
          $output .= \Krexx\Render::renderSingleChild($step_data['sourcecode'], 'Sourcecode', '. . .', TRUE, 'PHP');
        }
        // Function.
        if (isset($step_data['function'])) {
          $output .= \Krexx\Render::renderSingleChild($step_data['function'], 'Last called function', $step_data['function'], FALSE, 'string ', strlen($step_data['function']));
        }
        // Object.
        if (isset($step_data['object'])) {
          $output .= \Krexx\Objects::analyseObject($step_data['object'], 'Calling object');
        }
        // Type.
        if (isset($step_data['type'])) {
          $output .= \Krexx\Render::renderSingleChild($step_data['type'], 'Call type', $step_data['type'], FALSE, 'string ', strlen($step_data['type']));
        }
        // Args.
        if (isset($step_data['args'])) {
          $output .= \Krexx\Variables::analyseArray($step_data['args'], 'Arguments from the call');
        }

        return $output;
      };
      $output .= \Krexx\Render::renderExpandableChild($name, $type, $anon_function, $parameter);
    }

    return $output;
  }
}
