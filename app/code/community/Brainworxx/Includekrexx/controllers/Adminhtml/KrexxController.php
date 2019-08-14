<?php
/**
 * @file
 * Magento backend controller for kreXX
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

class Brainworxx_Includekrexx_Adminhtml_KrexxController extends Mage_Adminhtml_Controller_Action {

  /**
   * Standard initilaizing actions.
   *
   * @return Brainworxx_Includekrexx_Adminhtml_KrexxdocuController
   *   Return $this for chaining.
   */
  protected function init() {
    $this->loadLayout();
    $this->_setActiveMenu('system/krexxdocu');
    $this->_addBreadcrumb(Mage::helper('adminhtml')->__('System'), Mage::helper('includekrexx')->__('kreXX quick docu'));
    return $this;
  }

  /**
   * The docu action only displays the help text
   */
  public function docuAction() {
    $this->init();
    $this->getLayout()->getBlock('head')->setTitle(Mage::helper('includekrexx')->__('kreXX quick docu'));
    $this->renderLayout();
  }

  /**
   * The edit action displays configuration editor
   */
  public function editAction() {
    $this->init();
    $this->getLayout()->getBlock('head')->setTitle(Mage::helper('includekrexx')->__('Edit kreXX config file'));
    $this->renderLayout();
  }

  /**
   * Saves the form data.
   */
  public function saveAction() {
    $arguments = $this->getRequest()->getPost();
    $ini = '';
    $all_ok = TRUE;

      // Iterating through the form.
      foreach ($arguments as $key => $data) {
        if (is_array($data)) {
          // We've got a mainkey.
          $key = htmlspecialchars($key);
          // Add it to the file.
          $ini .= '[' . $key . ']' . PHP_EOL;
          foreach ($data as $attribute => $value) {
            $value = htmlspecialchars(preg_replace('/\s+/', '', $value));
            $attribute = htmlspecialchars($attribute);
            // Evaluate the setting!
            $all_ok = \krexx\Config::evaluateSetting($value, $attribute);
            if (!$all_ok) {
              break;
            }
            $ini .= $attribute . '=' . '"' . $value . '"' . PHP_EOL;
          }
          if (!$all_ok) {
            break;
          }
        }
      }
      // Now we should write the file!
      if ($all_ok) {
        // Ini already exists and is writeable.
        $ini_is_ok = file_exists(\krexx\Config::getPathToIni()) && is_writeable(\krexx\Config::getPathToIni());
        // Ini does not exist and directory is writeable.
        $dir_is_ok = !file_exists(\krexx\Config::getPathToIni()) && is_writeable(KREXXDIR);
        if ($ini_is_ok || $dir_is_ok) {
          file_put_contents(\krexx\Config::getPathToIni(), $ini);
        }
        else {
          $all_ok = FALSE;
          \krexx\Messages::addMessage('Configuration file ' . \krexx\Config::getPathToIni() . ' is not writeable!');
        }
      }
      // Something went wrong, we need to tell the user.
      if (!$all_ok) {
        Mage::getSingleton('core/session')->addError('The settings were NOT saved:' . \krexx\Messages::outputMessages());
      }
      else {
        Mage::getSingleton('core/session')->addSuccess("The settings were saved to: <br /> " . \krexx\Config::getPathToIni());
      }

    $this->_redirect('*/*/edit');


  }

}
