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
 * Logfile controller for backend logfile access.
 */
class Brainworxx_Includekrexx_Adminhtml_KrexxlogController extends Mage_Adminhtml_Controller_Action
{


    /**
     * Internal security call, to show if the current backenduser is allowed here.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/krexx/krexxlog');
    }

    /**
     * Standard initilaizing actions.
     *
     * @return Brainworxx_Includekrexx_Adminhtml_KrexxlogController
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
     * Display a listz of all logfiles.
     */
    public function indexAction()
    {
        $this->init();
        $this->getLayout()->getBlock('head')->setTitle(Mage::helper('includekrexx')->__('Access logfiles'));
        $this->renderLayout();
    }

    /**
     * Gets the content of a logfile
     */
    public function getContentAction()
    {
        $storage = \Krexx::$storage;

        // No directory traversal for you!
        $id = preg_replace('/[^0-9]/', '', $this->getRequest()->get('id'));
        // Get the filepath.
        $file = $storage->config->krexxdir . 'log' . DIRECTORY_SEPARATOR . $id . '.Krexx.html';

        $ioFile = new Varien_Io_File();
        if ($ioFile->streamOpen($file, 'rb')) {
            // Dispatch it!
            $stream = $ioFile->streamRead();
            while ($stream !== false) {
                echo $stream;
                // Use output buffering.
                ob_flush();
                flush();
                // Get new data.
                $stream = $ioFile->streamRead();
            }
            $ioFile->streamClose();
        } else {
            Mage::getSingleton('core/session')->addError('File: ' . $file . ' is not readable!');
        }
    }
}
