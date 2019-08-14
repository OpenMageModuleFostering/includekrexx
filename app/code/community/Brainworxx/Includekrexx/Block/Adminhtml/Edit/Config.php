<?php
/**
 * @file
 *   Magento backend block for kreXX
 *   kreXX: Krumo eXXtended
 *
 *   This is a debugging tool, which displays structured information
 *   about any PHP object. It is a nice replacement for print_r() or var_dump()
 *   which are used by a lot of PHP developers.
 *
 *   kreXX is a fork of Krumo, which was originally written by:
 *   Kaloyan K. Tsvetkov <kaloyan@kaloyan.info>
 *
 * @author brainworXX GmbH <info@brainworxx.de>
 *
 * @license http://opensource.org/licenses/LGPL-2.1
 *   GNU Lesser General Public License Version 2.1
 *
 *   kreXX Copyright (C) 2014-2015 Brainworxx GmbH
 *
 *   This library is free software; you can redistribute it and/or modify it
 *   under the terms of the GNU Lesser General Public License as published by
 *   the Free Software Foundation; either version 2.1 of the License, or (at
 *   your option) any later version.
 *   This library is distributed in the hope that it will be useful, but WITHOUT
 *   ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 *   FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public License
 *   for more details.
 *   You should have received a copy of the GNU Lesser General Public License
 *   along with this library; if not, write to the Free Software Foundation,
 *   Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
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
    $help['skin'] = htmlspecialchars(strip_tags(\Brainworxx\Krexx\View\Help::getHelp('skin')));
    $help['memoryLeft'] = htmlspecialchars(strip_tags(\Brainworxx\Krexx\View\Help::getHelp('memoryLeft')));
    $help['maxRuntime'] = htmlspecialchars(strip_tags(\Brainworxx\Krexx\View\Help::getHelp('maxRuntime')));
    $help['folder'] = htmlspecialchars(strip_tags(\Brainworxx\Krexx\View\Help::getHelp('folder')));
    $help['maxfiles'] = htmlspecialchars(strip_tags(\Brainworxx\Krexx\View\Help::getHelp('maxfiles')));
    $help['destination'] = htmlspecialchars(strip_tags(\Brainworxx\Krexx\View\Help::getHelp('destination')));
    $help['maxCall'] = htmlspecialchars(strip_tags(\Brainworxx\Krexx\View\Help::getHelp('maxCall')));
    $help['disabled'] = 'Here you can disable kreXX without uninstalling the whole module.';
    $help['detectAjax'] = htmlspecialchars(strip_tags(\Brainworxx\Krexx\View\Help::getHelp('detectAjax')));
    $help['analyseProtected'] = htmlspecialchars(strip_tags(\Brainworxx\Krexx\View\Help::getHelp('analyseProtected')));
    $help['analysePrivate'] = htmlspecialchars(strip_tags(\Brainworxx\Krexx\View\Help::getHelp('analysePrivate')));
    $help['analyseTraversable'] = htmlspecialchars(strip_tags(\Brainworxx\Krexx\View\Help::getHelp('analyseTraversable')));
    $help['debugMethods'] = "Comma-separated list of used debug callback functions. kreXX will try to call them, if they are available and display their provided data.\nWe Recommend for Magento: '__toArray,toString'";
    $help['level'] = htmlspecialchars(strip_tags(\Brainworxx\Krexx\View\Help::getHelp('level')));
    $help['analyseMethodsAtall'] = htmlspecialchars(strip_tags(\Brainworxx\Krexx\View\Help::getHelp('analyseMethodsAtall')));
    $help['analyseProtectedMethods'] = htmlspecialchars(strip_tags(\Brainworxx\Krexx\View\Help::getHelp('analyseProtectedMethods')));
    $help['analysePrivateMethods'] = htmlspecialchars(strip_tags(\Brainworxx\Krexx\View\Help::getHelp('analysePrivateMethods')));
    $help['registerAutomatically'] = htmlspecialchars(strip_tags(\Brainworxx\Krexx\View\Help::getHelp('registerAutomatically')));
    $help['backtraceAnalysis'] = htmlspecialchars(strip_tags(\Brainworxx\Krexx\View\Help::getHelp('backtraceAnalysis')));
    $this->assign('help', $help);


    // Initializing the select data for the template.
    $this->setSelectDestination(array('frontend' => 'frontend', 'file' => 'file'));
    $this->setSelectBool(array('true' => 'true', 'false' => 'false'));
    $this->setSelectBacktrace(array('normal' => 'normal', 'deep' => 'deep'));
    $skins = array();
    foreach (\Brainworxx\Krexx\View\Render::getSkinList() as $skin) {
      $skins[$skin] = $skin;
    }

    // Get all values from the configuration file.
    $settings['render']['skin'] = \Brainworxx\Krexx\Framework\Config::getConfigFromFile('render', 'skin');
    $settings['render']['memoryLeft'] = \Brainworxx\Krexx\Framework\Config::getConfigFromFile('render', 'memoryLeft');
    $settings['render']['maxRuntime'] = \Brainworxx\Krexx\Framework\Config::getConfigFromFile('render', 'maxRuntime');
    $settings['logging']['folder'] = \Brainworxx\Krexx\Framework\Config::getConfigFromFile('logging', 'folder');
    $settings['logging']['maxfiles'] = \Brainworxx\Krexx\Framework\Config::getConfigFromFile('logging', 'maxfiles');
    $settings['output']['destination'] = \Brainworxx\Krexx\Framework\Config::getConfigFromFile('output', 'destination');
    $settings['output']['maxCall'] = \Brainworxx\Krexx\Framework\Config::getConfigFromFile('output', 'maxCall');
    $settings['output']['disabled'] = \Brainworxx\Krexx\Framework\Config::getConfigFromFile('output', 'disabled');
    $settings['output']['detectAjax'] = \Brainworxx\Krexx\Framework\Config::getConfigFromFile('output', 'detectAjax');
    $settings['deep']['analyseProtected'] = \Brainworxx\Krexx\Framework\Config::getConfigFromFile('deep', 'analyseProtected');
    $settings['deep']['analysePrivate'] = \Brainworxx\Krexx\Framework\Config::getConfigFromFile('deep', 'analysePrivate');
    $settings['deep']['analyseTraversable'] = \Brainworxx\Krexx\Framework\Config::getConfigFromFile('deep', 'analyseTraversable');
    $settings['deep']['debugMethods'] = \Brainworxx\Krexx\Framework\Config::getConfigFromFile('deep', 'debugMethods');
    $settings['deep']['level'] = \Brainworxx\Krexx\Framework\Config::getConfigFromFile('deep', 'level');
    $settings['methods']['analyseMethodsAtall'] = \Brainworxx\Krexx\Framework\Config::getConfigFromFile('methods', 'analyseMethodsAtall');
    $settings['methods']['analyseProtectedMethods'] = \Brainworxx\Krexx\Framework\Config::getConfigFromFile('methods', 'analyseProtectedMethods');
    $settings['methods']['analysePrivateMethods'] = \Brainworxx\Krexx\Framework\Config::getConfigFromFile('methods', 'analysePrivateMethods');
    $settings['errorHandling']['registerAutomatically'] = \Brainworxx\Krexx\Framework\Config::getConfigFromFile('errorHandling', 'registerAutomatically');
    $settings['errorHandling']['backtraceAnalysis'] = \Brainworxx\Krexx\Framework\Config::getConfigFromFile('errorHandling', 'backtraceAnalysis');

    // Are these actually set?
    foreach ($settings as $mainkey => $setting) {
      foreach ($setting as $attribute => $config) {
        if (is_null($config)) {
          $factory[$attribute] = ' checked="checked" ';
          // We need to fill these values with the stuff from the factory settings!
          $settings[$mainkey][$attribute] = \Brainworxx\Krexx\Framework\Config::$configFallback[$mainkey][$attribute];
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
