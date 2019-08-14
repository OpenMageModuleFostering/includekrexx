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
 *   kreXX Copyright (C) 2014-2016 Brainworxx GmbH
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

use Brainworxx\Krexx\View\Help;
use Brainworxx\Krexx\Framework\Config;
use Brainworxx\Krexx\View\Messages;

/**
 * Class Brainworxx_Includekrexx_Block_Adminhtml_Edit_Config
 */
class Brainworxx_Includekrexx_Block_Adminhtml_Edit_Config extends Mage_Adminhtml_Block_Template
{


    /**
     * Assign the values to the template file.
     *
     * @see Mage_Core_Block_Template::_construct()
     */
    public function _construct()
    {
        parent::_construct();

        $help = array();
        $settings = array();
        $factory = array();

        // Initialzing help data for the template.
        $help['skin'] = htmlspecialchars(strip_tags(Help::getHelp('skin')));
        $help['memoryLeft'] = htmlspecialchars(strip_tags(Help::getHelp('memoryLeft')));
        $help['maxRuntime'] = htmlspecialchars(strip_tags(Help::getHelp('maxRuntime')));
        $help['folder'] = htmlspecialchars(strip_tags(Help::getHelp('folder')));
        $help['maxfiles'] = htmlspecialchars(strip_tags(Help::getHelp('maxfiles')));
        $help['destination'] = htmlspecialchars(strip_tags(Help::getHelp('destination')));
        $help['maxCall'] = htmlspecialchars(strip_tags(Help::getHelp('maxCall')));
        $help['disabled'] = 'Here you can disable kreXX without uninstalling the whole module.';
        $help['detectAjax'] = htmlspecialchars(strip_tags(Help::getHelp('detectAjax')));
        $help['analyseProtected'] = htmlspecialchars(strip_tags(Help::getHelp('analyseProtected')));
        $help['analysePrivate'] = htmlspecialchars(strip_tags(Help::getHelp('analysePrivate')));
        $help['analyseTraversable'] = htmlspecialchars(strip_tags(Help::getHelp('analyseTraversable')));
        $help['debugMethods'] = 'Comma-separated list of used debug callback functions. kreXX will try to call them,' .
            "if they are available and display their provided data.\nWe Recommend for Magento: '__toArray,toString'";
        $help['level'] = htmlspecialchars(strip_tags(Help::getHelp('level')));
        $help['analyseMethodsAtall'] = htmlspecialchars(strip_tags(Help::getHelp('analyseMethodsAtall')));
        $help['analyseProtectedMethods'] = htmlspecialchars(strip_tags(Help::getHelp('analyseProtectedMethods')));
        $help['analysePrivateMethods'] = htmlspecialchars(strip_tags(Help::getHelp('analysePrivateMethods')));
        $help['registerAutomatically'] = htmlspecialchars(strip_tags(Help::getHelp('registerAutomatically')));
        $help['backtraceAnalysis'] = htmlspecialchars(strip_tags(Help::getHelp('backtraceAnalysis')));
        $help['analyseConstants'] = htmlspecialchars(strip_tags(Help::getHelp('analyseConstants')));
        $this->assign('help', $help);

        // Initializing the select data for the template.
        $this->setSelectDestination(array(
            'frontend' => 'frontend',
            'file' => 'file'
        ));
        $this->setSelectBool(array('true' => 'true', 'false' => 'false'));
        $this->setSelectBacktrace(array(
            'normal' => 'normal',
            'deep' => 'deep'
        ));
        $skins = array();
        foreach (\Brainworxx\Krexx\View\Render::getSkinList() as $skin) {
            $skins[$skin] = $skin;
        }

        // Get all values from the configuration file.
        $settings['output']['skin'] = Config::getConfigFromFile(
            'output',
            'skin'
        );
        $settings['runtime']['memoryLeft'] = Config::getConfigFromFile(
            'runtime',
            'memoryLeft'
        );
        $settings['runtime']['maxRuntime'] = Config::getConfigFromFile(
            'runtime',
            'maxRuntime'
        );
        $settings['output']['folder'] = Config::getConfigFromFile(
            'output',
            'folder'
        );
        $settings['output']['maxfiles'] = Config::getConfigFromFile(
            'output',
            'maxfiles'
        );
        $settings['output']['destination'] = Config::getConfigFromFile(
            'output',
            'destination'
        );
        $settings['runtime']['maxCall'] = Config::getConfigFromFile(
            'runtime',
            'maxCall'
        );
        $settings['runtime']['disabled'] = Config::getConfigFromFile(
            'runtime',
            'disabled'
        );
        $settings['runtime']['detectAjax'] = Config::getConfigFromFile(
            'runtime',
            'detectAjax'
        );
        $settings['properties']['analyseProtected'] = Config::getConfigFromFile(
            'properties',
            'analyseProtected'
        );
        $settings['properties']['analysePrivate'] = Config::getConfigFromFile(
            'properties',
            'analysePrivate'
        );
        $settings['properties']['analyseConstants'] = Config::getConfigFromFile(
            'properties',
            'analyseConstants'
        );
        $settings['properties']['analyseTraversable'] = Config::getConfigFromFile(
            'properties',
            'analyseTraversable'
        );
        $settings['methods']['debugMethods'] = Config::getConfigFromFile(
            'methods',
            'debugMethods'
        );
        $settings['runtime']['level'] = Config::getConfigFromFile(
            'runtime',
            'level'
        );
        $settings['methods']['analyseMethodsAtall'] = Config::getConfigFromFile(
            'methods',
            'analyseMethodsAtall'
        );
        $settings['methods']['analyseProtectedMethods'] = Config::getConfigFromFile(
            'methods',
            'analyseProtectedMethods'
        );
        $settings['methods']['analysePrivateMethods'] = Config::getConfigFromFile(
            'methods',
            'analysePrivateMethods'
        );
        $settings['backtraceAndError']['registerAutomatically'] = Config::getConfigFromFile(
            'backtraceAndError',
            'registerAutomatically'
        );
        $settings['backtraceAndError']['backtraceAnalysis'] = Config::getConfigFromFile(
            'backtraceAndError',
            'backtraceAnalysis'
        );

        // Are these actually set?
        foreach ($settings as $mainkey => $setting) {
            foreach ($setting as $attribute => $config) {
                if (is_null($config)) {
                    $factory[$attribute] = ' checked="checked" ';
                    // We need to fill these values with the stuff from the factory settings!
                    $settings[$mainkey][$attribute] = Config::$configFallback[$mainkey][$attribute];
                } else {
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
    public function getSaveUrl()
    {
        return $this->getUrl('*/*/saveconfig', array(
            '_current' => true,
            'back' => null
        ));
    }
}
