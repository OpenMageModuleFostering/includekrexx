<?php
/**
 * @file
 * Output string handling for kreXX
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

namespace Krexx;


/**
 * Sends the kreXX output in the shutdown phase.
 *
 * @package Krexx
 */
class ShutdownHandler {

  /**
   * [0] -> The chunkedup string, that we intend to send to
   *        the browser.
   * [1] -> Are we ignoring local settings?
   *
   * @var array
   */
  protected $chunkStrings = array();

  /**
   * Adds output to our shutdownhandler.
   *
   * @param string $chunk_string
   *   The chunked output string.
   * @param bool $ignore_local_settings
   *   Weather or not we ignore local settings.
   */
  public function addChunkString($chunk_string, $ignore_local_settings = FALSE) {

    $this->chunkStrings[] = array($chunk_string, $ignore_local_settings);
  }

  /**
   * The shutdown callback.
   *
   * It gets called when PHP is sutting down. It will render
   * out kreXX output, to guarantie minimal interference with
   * the hosting CMS.
   *
   * @todo We need to call the renderer directly.
   *
   */
  public function shutdownCallback() {
    foreach ($this->chunkStrings as $chunk_string) {
      Toolbox::outputNow($chunk_string[0], $chunk_string[1]);
    }
  }
}
