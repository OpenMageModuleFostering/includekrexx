<?php
/**
 * @file
 * Helptexts for kreXX
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
 * Helptexts for kreXX.
 *
 * @package Krexx
 */
class Help {

  // A simpe array to hold the values.
  // There should not be any string collusions.
  protected static $helpArray = array(
    'localFunction' => 'Here you can enter your own alias function for \krexx::open().<br/> Example: When you enter \'gue\', the function will be \krexx::gue($myObject); [or krexx($myObject, \'gue\');],<br/> which only devs can use who have set the same value.This is useful, to prevent other devs from calling your debug functions.',
    'analyseProtected' => 'Shall kreXX try to analyse the protected properties of a class?<br/> This may result in a lot of output.',
    'analysePrivate' => 'Shall kreXX try to analyse the private properties of a class?<br/> This may result in a lot of output.',
    'analyseTraversable' => 'Shall kreXX try to analyse possible traversable data?<br/> Depending on the underlying framework this info might be covered by the debug callback functions.',
    'debugMethods' => 'Comma-separated list of used debug callback functions. A lot of frameworks offer these, toArray and toString beeing the most common.<br/> kreXX will try to call them, if they are available and display their provided data.<br/> You can not change them on the frontend. If you want other settigns here, you have to edit the kreXX configuration file.',
    'level' => 'Some frameworks have objects inside of objects inside of objects, and so on.<br/> Normally kreXX does not run in circles, but going to deep inside of an object tree can result in a lot of output. ',
    'resetbutton' => 'Here you can reset your local settings, which are stored in a cookie.<br/> kreXX will then use the global settings (either ini-file or factory settings).' ,
    'destination' => 'kreXX can save it\'s output to a file, instead of outputting it to the frontend.<br/> The output will then be stored in the log folder.',
    'useCookies' => 'You might want to hide kreXX from some project members. Web designers might not want to see it\'s output,<br/> especially when a lot of HTML markup is created (not to mention the delay). kreXX is not only hidden,<br/> it simply will not execute, when this option is used and you do not have a debug cookie in your browser.<br/> To get a debug cookie, please use the "Toggle debug cookie" button on the buttom.',
    'maxCall' => 'A lot of output does not only slow down your server, it also slows down your browser. When using kreXX in a loop,<br/> it will create output every time the loop is executed. To limit this, you can configure the maximum call settings.',
    'disabled' => 'Here you can disable kreXX. Note that this is just a local setting, it does not affect other browsers.',
    'folder' => 'This is the folder where kreXX will store it\'s logfiles.',
    'maxfiles' => 'How many logfiles do you want to store inside your logging folder?<br/> When there are more files than this number, the older files will get deleted.',
    'skin' => 'Choose a skin here. We have provided kreXX with two skins: "schablon" and "hans".',
    'jsLib' => 'kreXX uses in the frontend jQuery. We have bundled it with jQuery 1.11.0, but it may interfere<br/> with the library that you use. To use your own libs, you can point kreXX to your jQuery file.<br /> Entering an empty value will prevent kreXX from loading any library.',
    'currentSettings' => 'kreXX\'s configuration can be edited here, changes will be stored in a cookie and overwrite the ini and factory settings.<br/> <strong>Please note, that these are only local settings. They only affect this browser.</strong>',
    'debugcookie' => 'Here you can toggle your debug cookie. This only works with the "useCookies" function above.',
    'registerAutomatically' => 'This option registers the fatal errorhandler as soon as kreXX is included. When a fatal error occures,<br/> kreXX will offer a backtrace and an analysis of the all objects in it. PHP always clears the stack in case of a fatal error,<br/> so kreXX has to keep track of it. <strong>Be warned:</strong> This option will dramatically slow down your requests. Use this only when you have to.<br/> It is by far better to register the errorhandler yourself with <strong>\krexx::registerFatal();</strong> and later unregister it<br/> with <strong>\krexx::unregisterFatal();</strong> tp prevent a slowdown.',
    'detectAjax' => 'kreXX tries to detect whether a request is made via ajax. When it is detected, it will do no output at all. The AJAX detection can be disabled here.',
    'backtraceAnalysis' => 'Shall kreXX do a "deep" analysis of  the backtrace? Be warned, a deep analysis can produce a lot of output.<br/> A "normal" analysis will use the configured settings, while a "deep" analysis will get as much data from the objects as possible.',
    'memoryLeft' => 'kreXX checks regularely how much memory is left. Here you can adjust the amount where it will trigger an emergengy break.<br />Unit of measurement is MB.',
    'maxRuntime' => 'kreXX checks during the analysis how much time has elapsed since start. Here you can adjust the amount where it will trigger an emergengy break.<br />Unit of measurement is seconds.',
    'analysePublicMethods' => 'Here you can toggle if kreXX shall analyse the public methods of a class.',
    'analyseProtectedMethods' => 'Here you can toggle if kreXX shall analyse the protected methods of a class.',
    'analysePrivateMethods' => 'Here you can toggle if kreXX shall analyse the private methods of a class.',
  );

  /**
   * Returns the helptext when found, otherwise returns an empty string.
   *
   * @param string $what
   *   The help ID from the array above.
   *
   * @return string
   *   The help text.
   */
  public static function getHelp($what) {
    $result = '';
    if (isset(self::$helpArray[$what])) {
      $result = self::$helpArray[$what];
    }
    return $result;
  }
}