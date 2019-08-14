<?php

/**
 * @file
 *   Magento backend controller for kreXX
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

use Brainworxx\Krexx\View\Messages;

/**
 * Class Brainworxx_Includekrexx_Adminhtml_KrexxController
 */
class Brainworxx_Includekrexx_Adminhtml_KrexxController extends Mage_Adminhtml_Controller_Action
{

    /**
     * List of all setting-manes for which we are accepting values.
     *
     * @var array
     */
    protected $allowedSettingsNames = array(
        'skin',
        'memoryLeft',
        'maxRuntime',
        'folder',
        'maxfiles',
        'destination',
        'maxCall',
        'disabled',
        'detectAjax',
        'analyseProtected',
        'analysePrivate',
        'analyseTraversable',
        'debugMethods',
        'level',
        'analyseMethodsAtall',
        'analyseProtectedMethods',
        'analysePrivateMethods',
        'registerAutomatically',
        'backtraceAnalysis',
        'analyseConstants',
    );

    /**
     * List of all sections for which we are accepting values
     *
     * @var array
     */
    protected $allowedSections = array(
        'runtime',
        'output',
        'properties',
        'methods',
        'backtraceAndError',
    );

    protected function _isAllowed()
    {
        $actionName = $this->getFullActionName();

        if ($actionName == 'adminhtml_krexx_config') {
            return Mage::getSingleton('admin/session')->isAllowed('system/krexx/edit');
        }
        if ($actionName == 'adminhtml_krexx_feconfig') {
            return Mage::getSingleton('admin/session')->isAllowed('system/krexx/editfe');
        }
        if ($actionName == 'adminhtml_krexx_editlocalconfig') {
            return Mage::getSingleton('admin/session')->isAllowed('system/krexx/editlocalconfig');
        }
        if ($actionName == 'adminhtml_krexx_docu') {
            return Mage::getSingleton('admin/session')->isAllowed('system/krexx/docu');
        }

        // Still here?
        return parent::_isAllowed();
    }

    /**
     * Standard initilaizing actions.
     *
     * @return Brainworxx_Includekrexx_Adminhtml_KrexxController
     *   Return $this for chaining.
     */
    protected function init()
    {
        Mage::helper('includekrexx')->relayMessages();

        $this->loadLayout();
        $this->_setActiveMenu('system/krexxdocu');
        $this->_addBreadcrumb(
            Mage::helper('adminhtml')->__('System'),
            Mage::helper('includekrexx')->__('kreXX quick docu')
        );
        return $this;
    }

    /**
     * The docu action only displays the help text.
     */
    public function docuAction()
    {
        $this->init();
        $this->getLayout()->getBlock('head')->setTitle(Mage::helper('includekrexx')->__('kreXX quick docu'));
        $this->renderLayout();
    }

    /**
     * The edit action displays configuration editor.
     */
    public function configAction()
    {
        $this->init();
        $this->getLayout()->getBlock('head')->setTitle(Mage::helper('includekrexx')->__('Edit kreXX Config File'));
        $this->renderLayout();
    }

    /**
     * Displays the fe editing config form.
     */
    public function feconfigAction()
    {
        $this->init();
        $this->getLayout()->getBlock('head')->setTitle(Mage::helper('includekrexx')->__('Administer krexX FE editing'));
        $this->renderLayout();
    }

    /**
     * Displays the krexx::editSettings() as well as some help text.
     */
    public function editlocalconfigAction()
    {
        $this->init();
        $this->getLayout()->getBlock('head')->setTitle(Mage::helper('includekrexx')->__('Edit local browser settings'));
        $this->renderLayout();

    }

    /**
     * Saves the form data.
     */
    public function saveconfigAction()
    {
        $arguments = $this->getRequest()->getPost();
        $all_ok = true;
        $filepath = \Brainworxx\Krexx\Framework\Config::getPathToIni();
        // We must preserve the section 'feEditing'.
        // Everything else will be overwritten.
        $old_values = parse_ini_file($filepath, true);
        $old_values = array('feEditing' => $old_values['feEditing']);

        // Iterating through the form.
        foreach ($arguments as $section => $data) {
            if (is_array($data) && in_array($section, $this->allowedSections)) {
                // We've got a section key.
                foreach ($data as $setting_name => $value) {
                    if (in_array($setting_name, $this->allowedSettingsNames)) {
                        // We escape the value, just in case, since we can not whitelist it.
                        $value = htmlspecialchars(preg_replace('/\s+/', '', $value));
                        // Evaluate the setting!
                        if (\Brainworxx\Krexx\Framework\Config::evaluateSetting($section, $setting_name, $value)) {
                            $old_values[$section][$setting_name] = $value;
                        } else {
                            // Validation failed! kreXX will generate a message, which we will
                            // display at the buttom.
                            $all_ok = false;
                        }
                    }
                }
            }
        }

        // Now we must create the ini file.
        $ini = '';
        foreach ($old_values as $key => $setting) {
            $ini .= '[' . $key . ']' . PHP_EOL;
            foreach ($setting as $setting_name => $value) {
                $ini .= $setting_name . ' = "' . $value . '"' . PHP_EOL;
            }
        }

        // Now we should write the file!
        if ($all_ok) {
            $file = new Varien_Io_File();
            if ($file->write($filepath, $ini) === false) {
                $all_ok = false;
                Messages::addMessage('Configuration file ' . $filepath . ' is not writeable!');
            }
        }

        // Something went wrong, we need to tell the user.
        if (!$all_ok) {
            Mage::getSingleton('core/session')->addError(
                strip_tags(Messages::outputMessages()),
                "The settings were NOT saved."
            );
        } else {
            Mage::getSingleton('core/session')->addSuccess(
                "The settings were saved to: <br /> " . $filepath,
                "The data was saved."
            );
        }

        $this->_redirect('*/*/config');

    }

    /**
     * Saves the fe editing config from the backendform.
     */
    public function savefeconfigAction()
    {
        $arguments = $this->getRequest()->getPost();
        $all_ok = true;
        $filepath = \Brainworxx\Krexx\Framework\Config::getPathToIni();
        // Whitelist of the vales we are accepting.
        $allowed_values = array('full', 'display', 'none');

        // Get the old values . . .
        $old_values = parse_ini_file($filepath, true);
        // . . . and remove our part.
        unset($old_values['feEditing']);

        // We need to correct the allowed settings, since we do not allow anything.
        unset($this->allowedSettingsNames['destination']);
        unset($this->allowedSettingsNames['folder']);
        unset($this->allowedSettingsNames['maxfiles']);
        unset($this->allowedSettingsNames['debugMethods']);

        // Iterating through the form.
        foreach ($arguments as $key => $data) {
            if (is_array($data)) {
                foreach ($data as $setting_name => $value) {
                    if (in_array($value, $allowed_values) && in_array($setting_name, $this->allowedSettingsNames)) {
                        // Whitelisted values are ok.
                        $old_values['feEditing'][$setting_name] = $value;
                    } else {
                        // Validation failed!
                        $all_ok = false;
                        Messages::addMessage(htmlentities($value) . ' is not an allowed value!');
                    }
                }
            }
        }

        // Now we must create the ini file.
        $ini = '';
        foreach ($old_values as $key => $setting) {
            $ini .= '[' . $key . ']' . PHP_EOL;
            foreach ($setting as $setting_name => $value) {
                $ini .= $setting_name . ' = "' . $value . '"' . PHP_EOL;
            }
        }

        // Now we should write the file!
        if ($all_ok) {
            $file = new Varien_Io_File();
            if ($file->write($filepath, $ini) === false) {
                $all_ok = false;
                Messages::addMessage('Configuration file ' . $filepath . ' is not writeable!');
            }
        }

        // Something went wrong, we need to tell the user.
        if (!$all_ok) {
            Mage::getSingleton('core/session')->addError(
                strip_tags(Messages::outputMessages()),
                "The settings were NOT saved."
            );
        } else {
            Mage::getSingleton('core/session')->addSuccess(
                "The settings were saved to: <br /> " . $filepath,
                "The data was saved."
            );
        }

        $this->_redirect('*/*/feconfig');
    }
}
