<?php
/**
 * @file
 * Event observer for kreXX
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

class Brainworxx_Includekrexx_Model_Observer {

  /**
   * Includes the kreXX mainfile
   *
   * @param Varien_Event_Observer $observer
   *   The event observer of the event we are listening to.
   */
  public function includeKreXX(Varien_Event_Observer $observer) {
    // We need to do this only once
    // the static should save some time.
    static $been_here = FALSE;
    if (!$been_here) {
      $filename = Mage::getModuleDir('Block', 'Brainworxx_Includekrexx') . '/Block/krexx/Krexx.php';
      if (file_exists($filename) && !class_exists('Krexx')) {
        include_once $filename;
      }
      $been_here = TRUE;
    }
  }
}
