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
 * Splitting strings into small tiny chunks.
 *
 * The mainproblem with our "templating engine" is, we are
 * adding partials into partials, over and over again. This
 * results in a very long string, 30 MB or larger. When using
 * str_replace() on it, we can have a memory peak of 90 MB or
 * more.
 * This class splits this string into small and good-to-handle
 * chunks. We also use this class stitch back together this
 * string for output.
 *
 * @see \Krexx\Variables::encodeString()
 *   We use '@@@' to mark a chunk key. This function escapes the @
 *   so we have no collusion with data from strings.
 *
 * @package Krexx
 */
class Chunks {

  /**
   * The minimum length of the chunck
   *
   * @var int
   */
  protected static $chunkLength = 10000;

  /**
   * Splits a string into small chunks.
   *
   * The chunks are saved to disk and later on
   *
   * @param string $string
   *   The data we want to split into chunks.
   *
   * @return string
   *   The key to the chunk, wrapped up in {}.
   */
  public static function chunkMe($string) {

    if (strlen($string) > self::$chunkLength) {
      // Get the key.
      $key = self::genKey();
      // Write the key to the chunks folder.
      if (is_writable(KREXXDIR . 'chunks/')) {
        // We only write when we are allowed.
        file_put_contents(KREXXDIR . 'chunks/' . $key . '.Krexx.tmp', $string);
      }
      // Return the first part plus the key.
      return '@@@' . $key . '@@@';
    }
    else {
      // Return the original, because it's too small.
      return $string;
    }
  }

  /**
   * Generates the chunck key.
   *
   * @return string
   *   The generated key.
   */
  protected static function genKey() {
    static $counter = 0;
    $counter++;

    return Toolbox::fileStamp() . '_' . $counter;
  }

  /**
   * Gets the original data from the string.
   *
   * Reads the data from a file in the chunks folder.
   * The output may contain other chunk keys.
   * nothing more then a wrapper for file_get_contents()
   *
   * @param string $key
   *   The key of the chunk of wich we want to get the data.
   *
   * @return string
   *   The original date
   *
   */
  protected static function dechunkMe($key) {
    $filename = KREXXDIR . 'chunks/' . $key . '.Krexx.tmp';
    if (is_writable($filename)) {
      // Read the file.
      $string = file_get_contents($filename);
      // Delete it, we don't need it anymore.
      unlink($filename);
    }
    else {
      // Huh, we can not fully access this one.
      $string = 'Could not access chunk file ' . $filename;
      Messages::addMessage('Could not access chunk file ' . $filename);
    }

    return $string;
  }

  /**
   * Replaces all chunk keys from a string with the original data.
   *
   * Send the output to the browser.
   *
   * @param string $string
   *   The chunck string.
   */
  public static function sendDechunkedToBrowser($string) {
    static $been_here = FALSE;

    self::cleanupOldChunks();

    $chunk_pos = strpos($string, '@@@');
    $counter = 0;

    // Here we have our SPOF in memory usage.
    // I've tried different things here:
    // - using echo only once
    // - write to disk and include it
    // - heredoc
    // - fwrite
    // Looks like we are stuck with this.
    // Writing it to a file produces no noticeable memory peak.
    // Since we might run into touble here, we need to check the
    // remaining memory.
    // Sadly, we will not be able to close the DOM gracefully,
    // because the render process can not really say where we exacly are.
    // We are chunking the output always after a node, but that is all that
    // is really sure. The only thing left is a simple JS alert . . .
    while ($chunk_pos !== FALSE) {
      $counter++;
      if (!Internals::checkEmergencyBreak()) {
        if (!$been_here) {
          // We display this only once.
          echo '<script>alert("Emergency break for large output.\n\nYou should try to switch to file output.");</script>';
        }
        $been_here = TRUE;
        // There might be some leftover chunks.
        // We delete them all!
        self::cleanupNewChunks();
        // We stop right here!
        die();
      }
      else {
        // We have a chunk, we send the html part.
        echo substr($string, 0, $chunk_pos);
        $chunk_part = substr($string, $chunk_pos);

        // We translate the first chunk.
        $result = explode('@@@', $chunk_part, 3);
        $string = str_replace('@@@' . $result[1] . '@@@', self::dechunkMe($result[1]), $chunk_part);
        $chunk_pos = strpos($string, '@@@');
      }
    }

    // No more chunk keys, we send what is left.
    echo $string;
    return TRUE;
  }

  /**
   * Replaces all chunk keys from a string with the original data.
   *
   * Saves the output to a file.
   *
   * @param string $filename
   *   The path and the filename, where we want to save it.
   * @param string $string
   *   The chunked version of the output.
   */
  public static function saveDechunkedToFile($filename, $string) {
    self::cleanupOldChunks();

    $chunk_pos = strpos($string, '@@@');

    while ($chunk_pos !== FALSE) {
      // We have a chunk, we send the html part.
      file_put_contents($filename, substr($string, 0, $chunk_pos), FILE_APPEND);
      $chunk_part = substr($string, $chunk_pos);

      // We translate the first chunk.
      // Strangely, with a memorypeak of 84MB, explode is
      // 2 mb cheaper than preg_match().
      $result = explode('@@@', $chunk_part, 3);
      $string = str_replace('@@@' . $result[1] . '@@@', self::dechunkMe($result[1]), $chunk_part);
      $chunk_pos = strpos($string, '@@@');
    }

    // No more chunks, we send what is left.
    file_put_contents($filename, $string, FILE_APPEND);
  }

  /**
   * Deletes chunk files older then 1 hour, in case there are some left.
   */
  protected static function cleanupOldChunks() {
    static $been_here = FALSE;

    // We only do this once.
    if (!$been_here) {
      // Clean up leftover files.
      $chunk_list = glob(KREXXDIR . 'chunks/*.Krexx.tmp');
      foreach ($chunk_list as $file) {
        if (filemtime($file) < time() - 3600) {
          unlink($file);
        }
      }
    }

    $been_here = TRUE;
  }

  /**
   * Deletes all chunks from the current run.
   */
  public static function cleanupNewChunks() {
    $chunk_list = glob(KREXXDIR . 'chunks/' . Toolbox::fileStamp() . '_*.Krexx.tmp');

    foreach ($chunk_list as $file) {
      unlink($file);
    }
  }
}
