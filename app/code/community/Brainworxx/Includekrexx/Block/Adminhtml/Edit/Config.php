<?php
/**
 * @file
 * Magento backend block for kreXX
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

class Brainworxx_Includekrexx_Block_Adminhtml_Edit_Config extends Mage_Adminhtml_Block_Template {


  /**
   * Assign the values to the template file.
   *
   * @see Mage_Core_Block_Template::_construct()
   */
  public function _construct() {
    parent::_construct();

    $help = array();
    $settings = array();
    $factory = array();

    // Initialzing help data for the template.
    $help['skin'] = htmlspecialchars(strip_tags(\Krexx\Help::getHelp('skin')));
    $help['jsLib'] = htmlspecialchars(strip_tags(\Krexx\Help::getHelp('jsLib')));
    $help['memoryLeft'] = htmlspecialchars(strip_tags(\Krexx\Help::getHelp('memoryLeft')));
    $help['maxRuntime'] = htmlspecialchars(strip_tags(\Krexx\Help::getHelp('maxRuntime')));
    $help['folder'] = htmlspecialchars(strip_tags(\Krexx\Help::getHelp('folder')));
    $help['maxfiles'] = htmlspecialchars(strip_tags(\Krexx\Help::getHelp('maxfiles')));
    $help['destination'] = htmlspecialchars(strip_tags(\Krexx\Help::getHelp('destination')));
    $help['useCookies'] = htmlspecialchars(strip_tags(\Krexx\Help::getHelp('useCookies')));
    $help['maxCall'] = htmlspecialchars(strip_tags(\Krexx\Help::getHelp('maxCall')));
    $help['disabled'] = 'Here you can disable kreXX without uninstalling the whole module.';
    $help['detectAjax'] = htmlspecialchars(strip_tags(\Krexx\Help::getHelp('detectAjax')));
    $help['analyseProtected'] = htmlspecialchars(strip_tags(\Krexx\Help::getHelp('analyseProtected')));
    $help['analysePrivate'] = htmlspecialchars(strip_tags(\Krexx\Help::getHelp('analysePrivate')));
    $help['analyseTraversable'] = htmlspecialchars(strip_tags(\Krexx\Help::getHelp('analyseTraversable')));
    $help['debugMethods'] = "Comma-separated list of used debug callback functions. kreXX will try to call them, if they are available and display their provided data.\nWe Recommend for Magento: '__toArray,toString'";
    $help['level'] = htmlspecialchars(strip_tags(\Krexx\Help::getHelp('level')));
    $help['analysePublicMethods'] = htmlspecialchars(strip_tags(\Krexx\Help::getHelp('analysePublicMethods')));
    $help['analyseProtectedMethods'] = htmlspecialchars(strip_tags(\Krexx\Help::getHelp('analyseProtectedMethods')));
    $help['analysePrivateMethods'] = htmlspecialchars(strip_tags(\Krexx\Help::getHelp('analysePrivateMethods')));
    $help['registerAutomatically'] = htmlspecialchars(strip_tags(\Krexx\Help::getHelp('registerAutomatically')));
    $help['backtraceAnalysis'] = htmlspecialchars(strip_tags(\Krexx\Help::getHelp('backtraceAnalysis')));
    $this->assign('help', $help);


    // Initializing the select data for the template.
    $this->setSelectDestination(array('frontend' => 'frontend', 'file' => 'file'));
    $this->setSelectBool(array('true' => 'true', 'false' => 'false'));
    $this->setSelectBacktrace(array('normal' => 'normal', 'deep' => 'deep'));
    $skins = array();
    foreach (Krexx\Render::getSkinList() as $skin) {
      $skins[$skin] = $skin;
    }

    // Get all values from the configuration file.
    $settings['render']['skin'] = \Krexx\Config::getConfigFromFile('render', 'skin');
    $settings['render']['jsLib'] = \Krexx\Config::getConfigFromFile('render', 'jsLib');
    $settings['render']['memoryLeft'] = \Krexx\Config::getConfigFromFile('render', 'memoryLeft');
    $settings['render']['maxRuntime'] = \Krexx\Config::getConfigFromFile('render', 'maxRuntime');
    $settings['logging']['folder'] = \Krexx\Config::getConfigFromFile('logging', 'folder');
    $settings['logging']['maxfiles'] = \Krexx\Config::getConfigFromFile('logging', 'maxfiles');
    $settings['output']['destination'] = \Krexx\Config::getConfigFromFile('output', 'destination');
    $settings['output']['useCookies'] = \Krexx\Config::getConfigFromFile('output', 'useCookies');
    $settings['output']['maxCall'] = \Krexx\Config::getConfigFromFile('output', 'maxCall');
    $settings['output']['disabled'] = \Krexx\Config::getConfigFromFile('output', 'disabled');
    $settings['output']['detectAjax'] = \Krexx\Config::getConfigFromFile('output', 'detectAjax');
    $settings['deep']['analyseProtected'] = \Krexx\Config::getConfigFromFile('deep', 'analyseProtected');
    $settings['deep']['analysePrivate'] = \Krexx\Config::getConfigFromFile('deep', 'analysePrivate');
    $settings['deep']['analyseTraversable'] = \Krexx\Config::getConfigFromFile('deep', 'analyseTraversable');
    $settings['deep']['debugMethods'] = \Krexx\Config::getConfigFromFile('deep', 'debugMethods');
    $settings['deep']['level'] = \Krexx\Config::getConfigFromFile('deep', 'level');
    $settings['methods']['analysePublicMethods'] = \Krexx\Config::getConfigFromFile('methods', 'analysePublicMethods');
    $settings['methods']['analyseProtectedMethods'] = \Krexx\Config::getConfigFromFile('methods', 'analyseProtectedMethods');
    $settings['methods']['analysePrivateMethods'] = \Krexx\Config::getConfigFromFile('methods', 'analysePrivateMethods');
    $settings['errorHandling']['registerAutomatically'] = \Krexx\Config::getConfigFromFile('errorHandling', 'registerAutomatically');
    $settings['errorHandling']['backtraceAnalysis'] = \Krexx\Config::getConfigFromFile('errorHandling', 'backtraceAnalysis');

    // Are these actually set?
    foreach ($settings as $mainkey => $setting) {
      foreach ($setting as $attribute => $config) {
        if (is_null($config)) {
          $factory[$attribute] = ' checked="checked" ';
          // We need to fill these values with the stuff from the factory settings!
          $settings[$mainkey][$attribute] = \Krexx\Config::$configFallback[$mainkey][$attribute];
        }
        else {
          $factory[$attribute] = '';
        }
      }
    }

    // Add them to the template.
    $this->assign('skins', $skins);
    $this->assign('settings', $settings);
    $this->assign('factory', $factory);
  }

  /**
   * Return save url for edit form
   *
   * @return string
   *   The url where the form is saved.
   */
  public function getSaveUrl() {
    return $this->getUrl('*/*/saveconfig', array('_current' => TRUE, 'back' => NULL));
  }

}
