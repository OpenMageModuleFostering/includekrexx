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
 *   kreXX Copyright (C) 2014-2017 Brainworxx GmbH
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
 * Event observer for kreXX, includes the mainlibrary at an early level.
 */
class Brainworxx_Includekrexx_Model_Observer
{

    /**
     * Includes the kreXX mainfile
     *
     * @param Varien_Event_Observer $observer
     *   The event observer of the event we are listening to.
     */
    public function includeKreXX(Varien_Event_Observer $observer)
    {
        // We need to do this only once
        // the static should save some time.
        static $beenHere = false;


        if (!$beenHere) {
            $blockPath = Mage::getModuleDir('Block', 'Brainworxx_Includekrexx');
            $filename = $blockPath . '/Block/krexx/Krexx.php';
            // Tell kreXX that we want to use some special classes for the getter analysis.
            if (!is_array($GLOBALS['kreXXoverwrites']['classes'])) {
                $GLOBALS['kreXXoverwrites']['classes'] = array();
            }
            $GLOBALS['kreXXoverwrites']['classes'] = array(
                'Brainworxx\\Krexx\\Analyse\\Callback\\Iterate\\ThroughGetter' => 'Brainworxx_Includekrexx_Model_Dynamicgetter',
                'Brainworxx\\Krexx\\Service\\Config\\Config' => 'Brainworxx_Includekrexx_Model_Config',
            );

            if (file_exists($filename) && !class_exists('Krexx', false)) {
                include_once $filename;
            }
            $beenHere = true;
        }
    }
}
