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

class Brainworxx_Includekrexx_Block_Adminhtml_Edit_Feconfig extends Mage_Adminhtml_Block_Template {


  /**
   * Return save url for edit form
   *
   * @return string
   *   The url, where the form is saved.
   */
  public function getSaveUrl() {
    return $this->getUrl('*/*/savefeconfig', array('_current' => TRUE, 'back' => NULL));
  }

  /**
   * Assign the values to the template file.
   *
   * @see Mage_Core_Block_Template::_construct()
   */
  public function _construct() {
    parent::_construct();

    // Generate the values for the select elements.
    $data = array();
    $settings = array();
    $factory = array();
    // Setting possible form values.
    $data['settings'] = array(
      'full' => 'full edit',
      'display' => 'display only',
      'none' => 'do not display',
    );

    // See, if we have any values in the configuration file.
    $settings['render']['skin'] = $this->convertKrexxFeSetting(\Krexx\Config::getFeConfigFromFile('skin'));
    $settings['render']['jsLib'] = $this->convertKrexxFeSetting(\Krexx\Config::getFeConfigFromFile('jsLib'));
    $settings['render']['memoryLeft'] = $this->convertKrexxFeSetting(\Krexx\Config::getFeConfigFromFile('memoryLeft'));
    $settings['render']['maxRuntime'] = $this->convertKrexxFeSetting(\Krexx\Config::getFeConfigFromFile('maxRuntime'));
    $settings['logging']['folder'] = $this->convertKrexxFeSetting(\Krexx\Config::getFeConfigFromFile('folder'));
    $settings['logging']['maxfiles'] = $this->convertKrexxFeSetting(\Krexx\Config::getFeConfigFromFile('maxfiles'));
    $settings['output']['destination'] = $this->convertKrexxFeSetting(\Krexx\Config::getFeConfigFromFile('destination'));
    $settings['output']['maxCall'] = $this->convertKrexxFeSetting(\Krexx\Config::getFeConfigFromFile('maxCall'));
    $settings['output']['disabled'] = $this->convertKrexxFeSetting(\Krexx\Config::getFeConfigFromFile('disabled'));
    $settings['output']['detectAjax'] = $this->convertKrexxFeSetting(\Krexx\Config::getFeConfigFromFile('detectAjax'));
    $settings['deep']['analyseProtected'] = $this->convertKrexxFeSetting(\Krexx\Config::getFeConfigFromFile('analyseProtected'));
    $settings['deep']['analysePrivate'] = $this->convertKrexxFeSetting(\Krexx\Config::getFeConfigFromFile('analysePrivate'));
    $settings['deep']['analyseTraversable'] = $this->convertKrexxFeSetting(\Krexx\Config::getFeConfigFromFile('analyseTraversable'));
    $settings['deep']['debugMethods'] = $this->convertKrexxFeSetting(\Krexx\Config::getFeConfigFromFile('debugMethods'));
    $settings['deep']['level'] = $this->convertKrexxFeSetting(\Krexx\Config::getFeConfigFromFile('level'));
    $settings['methods']['analysePublicMethods'] = $this->convertKrexxFeSetting(\Krexx\Config::getFeConfigFromFile('analysePublicMethods'));
    $settings['methods']['analyseProtectedMethods'] = $this->convertKrexxFeSetting(\Krexx\Config::getFeConfigFromFile('analyseProtectedMethods'));
    $settings['methods']['analysePrivateMethods'] = $this->convertKrexxFeSetting(\Krexx\Config::getFeConfigFromFile('analysePrivateMethods'));
    $settings['errorHandling']['registerAutomatically'] = $this->convertKrexxFeSetting(\Krexx\Config::getFeConfigFromFile('registerAutomatically'));
    $settings['errorHandling']['backtraceAnalysis'] = $this->convertKrexxFeSetting(\Krexx\Config::getFeConfigFromFile('backtraceAnalysis'));

    // Are these actually set?
    foreach ($settings as $mainkey => $setting) {
      foreach ($setting as $attribute => $config) {
        if (is_null($config)) {
          $factory[$attribute] = ' checked="checked" ';
          // We need to fill these values with the stuff from the
          // factory settings!
          $settings[$mainkey][$attribute] = $this->convertKrexxFeSetting(\Krexx\Config::$feConfigFallback[$attribute]);
        }
        else {
          $factory[$attribute] = '';
        }
      }
    }

    $this->assign('data', $data);
    $this->assign('settings', $settings);
    $this->assign('factory', $factory);
  }

  /**
   * Converts the kreXX FE config setting.
   *
   * Letting people choose what kind of form element will
   * be used does not really make sense. We will convert the
   * original kreXX settings to a more useable form for the editor.
   *
   * @param array $values
   *   The values we want to convert.
   */
  protected function convertKrexxFeSetting($values) {
    if (is_array($values)) {
      // The values are:
      // full    -> is editable and values will be accepted
      // display -> we will only display the settings
      // The original values include the name of a template partial
      // with the form element.
      if ($values['type'] == 'None') {
        // It's not visible, thus we do not accept any values from it.
        $result = 'none';
      }
      if ($values['editable'] == 'true' && $values['type'] != 'None') {
        // It's editable and visible.
        $result = 'full';
      }
      if ($values['editable'] == 'false' && $values['type'] != 'None') {
        // It's only visible.
        $result = 'display';
      }
      return $result;
    }
  }
}
