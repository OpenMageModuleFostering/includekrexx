<?php
/**
 * kreXX: Krumo eXXtended
 *
 * kreXX is a debugging tool, which displays structured information
 * about any PHP object. It is a nice replacement for print_r() or var_dump()
 * which are used by a lot of PHP developers.
 *
 * kreXX is a fork of Krumo, which was originally written by:
 * Kaloyan K. Tsvetkov <kaloyan@kaloyan.info>
 *
 * @author
 *   brainworXX GmbH <info@brainworxx.de>
 *
 * @license
 *   http://opensource.org/licenses/LGPL-2.1
 *
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


/**
 * Block for the configuration file editor in the backend.
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
        $storage = \Krexx::$storage;

        // Initialzing help data for the template.
        $help['skin'] = htmlspecialchars(strip_tags($storage->messages->getHelp('skin')));
        $help['iprange'] = htmlspecialchars(strip_tags($storage->messages->getHelp('iprange')));
        $help['maxfiles'] = htmlspecialchars(strip_tags($storage->messages->getHelp('maxfiles')));
        $help['destination'] = htmlspecialchars(strip_tags($storage->messages->getHelp('destination')));
        $help['maxCall'] = htmlspecialchars(strip_tags($storage->messages->getHelp('maxCall')));
        $help['disabled'] = 'Here you can disable kreXX without uninstalling the whole module.';
        $help['detectAjax'] = htmlspecialchars(strip_tags($storage->messages->getHelp('detectAjax')));
        $help['analyseProtected'] = htmlspecialchars(strip_tags($storage->messages->getHelp('analyseProtected')));
        $help['analysePrivate'] = htmlspecialchars(strip_tags($storage->messages->getHelp('analysePrivate')));
        $help['analyseTraversable'] = htmlspecialchars(strip_tags($storage->messages->getHelp('analyseTraversable')));
        $help['debugMethods'] = 'Comma-separated list of used debug callback functions. kreXX will try to call them,' .
            "if they are available and display their provided data.\nWe Recommend for Magento: '__toArray,toString'";
        $help['level'] = htmlspecialchars(strip_tags($storage->messages->getHelp('level')));
        $help['analyseProtectedMethods'] = htmlspecialchars(strip_tags($storage->messages->getHelp('analyseProtectedMethods')));
        $help['analysePrivateMethods'] = htmlspecialchars(strip_tags($storage->messages->getHelp('analysePrivateMethods')));
        $help['registerAutomatically'] = htmlspecialchars(strip_tags($storage->messages->getHelp('registerAutomatically')));
        $help['analyseConstants'] = htmlspecialchars(strip_tags($storage->messages->getHelp('analyseConstants')));
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

        foreach ($storage->render->getSkinList() as $skin) {
            $skins[$skin] = $skin;
        }

        // Get all values from the configuration file.
        $settings['output']['skin'] = $storage->config->getConfigFromFile(
            'output',
            'skin'
        );
        $settings['output']['maxfiles'] = $storage->config->getConfigFromFile(
            'output',
            'maxfiles'
        );
        $settings['output']['destination'] = $storage->config->getConfigFromFile(
            'output',
            'destination'
        );
        $settings['runtime']['maxCall'] = $storage->config->getConfigFromFile(
            'runtime',
            'maxCall'
        );
        $settings['runtime']['disabled'] = $storage->config->getConfigFromFile(
            'runtime',
            'disabled'
        );
        $settings['runtime']['iprange'] = $storage->config->getConfigFromFile(
            'runtime',
            'iprange'
        );
        $settings['runtime']['detectAjax'] = $storage->config->getConfigFromFile(
            'runtime',
            'detectAjax'
        );
        $settings['properties']['analyseProtected'] = $storage->config->getConfigFromFile(
            'properties',
            'analyseProtected'
        );
        $settings['properties']['analysePrivate'] = $storage->config->getConfigFromFile(
            'properties',
            'analysePrivate'
        );
        $settings['properties']['analyseConstants'] = $storage->config->getConfigFromFile(
            'properties',
            'analyseConstants'
        );
        $settings['properties']['analyseTraversable'] = $storage->config->getConfigFromFile(
            'properties',
            'analyseTraversable'
        );
        $settings['methods']['debugMethods'] = $storage->config->getConfigFromFile(
            'methods',
            'debugMethods'
        );
        $settings['runtime']['level'] = $storage->config->getConfigFromFile(
            'runtime',
            'level'
        );
        $settings['methods']['analyseProtectedMethods'] = $storage->config->getConfigFromFile(
            'methods',
            'analyseProtectedMethods'
        );
        $settings['methods']['analysePrivateMethods'] = $storage->config->getConfigFromFile(
            'methods',
            'analysePrivateMethods'
        );
        $settings['backtraceAndError']['registerAutomatically'] = $storage->config->getConfigFromFile(
            'backtraceAndError',
            'registerAutomatically'
        );

        // Are these actually set?
        foreach ($settings as $mainkey => $setting) {
            foreach ($setting as $attribute => $config) {
                if (is_null($config)) {
                    $factory[$attribute] = ' checked="checked" ';
                    // We need to fill these values with the stuff from the factory settings!
                    $settings[$mainkey][$attribute] = $storage->config->configFallback[$mainkey][$attribute];
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
