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

namespace Brainworxx\Krexx\View\Output;

use Brainworxx\Krexx\Service\Factory\Pool;

/**
 * Output string handling for kreXX, splitting strings into small tiny chunks.
 *
 * The main problem with our "templating engine" is, we are
 * adding partials into partials, over and over again. This
 * results in a very long string, 30 MB or larger. When using
 * str_replace() on it, we can have a memory peak of 90 MB or
 * more.
 * This class splits this string into small and good-to-handle
 * chunks. We also use this class stitch back together this
 * string for output.
 *
 * @see \Brainworxx\Krexx\Service\Factory\Pool->encodeString()
 *   We use '@@@' to mark a chunk key. This function escapes the @
 *   so we have no collusion with data from strings.
 *
 * @package Brainworxx\Krexx\Service\Misc
 */
class Chunks
{
    /**
     * Here we store all relevant data.
     *
     * @var Pool
     */
    protected $pool;

    /**
     * Here we store the metadata from the call.
     *
     * We save this data in a separate file, so that a backend extension can offer
     * some additional data about the logfiles and their content.
     *
     * @var array
     */
    protected $metadata = array();

    /**
     * Are we using chunks?
     *
     * When we do, kreXX will store temporary files in the chunks folder.
     * This saves a lot of memory!
     *
     * @var bool
     */
    protected $useChunks = true;

    /**
     * The file service used to read and write files.
     *
     * @var \Brainworxx\Krexx\Service\Misc\File
     */
    protected $fileService;

    /**
     * The logfolder.
     *
     * @var string
     */
    protected $logDir;

    /**
     * The folder for the output chunks.
     *
     * @var string
     */
    protected $chunkDir;

    /**
     * Injects the pool.
     *
     * @param Pool $pool
     *   The pool, where we store the classes we need.
     */
    public function __construct(Pool $pool)
    {
        $this->pool = $pool;
        $this->chunkDir = $pool->config->getChunkDir();
        $this->logDir = $pool->config->getLogDir();
        $this->fileService = $pool->createClass('Brainworxx\\Krexx\\Service\\Misc\\File');
    }

    /**
     * Splits a string into small chunks.
     *
     * The chunks are saved to disk and later on.
     *
     * @param string $string
     *   The data we want to split into chunks.
     *
     * @return string
     *   The key to the chunk, wrapped up in @@@@@@.
     */
    public function chunkMe($string)
    {
        if ($this->useChunks && strlen($string) > 10000) {
            // Get the key.
            $key = $this->genKey();
            // Write the key to the chunks folder.
            $this->fileService->putFileContents($this->chunkDir . $key . '.Krexx.tmp', $string);
            // Return the first part plus the key.
            return '@@@' . $key . '@@@';
        } else {
            // Return the original, because it's too small.
            return $string;
        }
    }

    /**
     * Generates the chunk key.
     *
     * @return string
     *   The generated key.
     */
    protected function genKey()
    {
        static $counter = 0;
        $counter++;

        return $this->fileService->fileStamp() . '_' . $counter;
    }

    /**
     * Gets the original data from the string.
     *
     * Reads the data from a file in the chunks folder.
     * The output may contain other chunk keys.
     * nothing more then a wrapper for file_get_contents()
     *
     * @param string $key
     *   The key of the chunk of which we want to get the data.
     *
     * @return string
     *   The original date
     *
     */
    protected function dechunkMe($key)
    {
        $filename = $this->chunkDir . $key . '.Krexx.tmp';
        if (is_writable($filename)) {
            // Read the file.
            $string = $this->fileService->getFileContents($filename);
            // Delete it, we don't need it anymore.
            unlink($filename);
        } else {
            // Huh, we can not fully access this one.
            $string = 'Could not access chunk file ' . $filename;
            $this->pool->messages->addMessage('Could not access chunk file ' . $filename);
        }

        return $string;
    }

    /**
     * Replaces all chunk keys from a string with the original data.
     *
     * Send the output to the browser.
     *
     * @param string $string
     *   The chunk string.
     */
    public function sendDechunkedToBrowser($string)
    {
        // Do some housekeeping. Unless something dreadful had happened, there
        // should not be anything to cleanup.
        $this->cleanupOldChunks();

        $chunkPos = strpos($string, '@@@');

        while ($chunkPos !== false) {
            // We have a chunk, we send the html part.
            echo substr($string, 0, $chunkPos);
            ob_flush();
            flush();
            $chunkPart = substr($string, $chunkPos);

            // We translate the first chunk.
            $result = explode('@@@', $chunkPart, 3);
            $string = str_replace('@@@' . $result[1] . '@@@', $this->dechunkMe($result[1]), $chunkPart);
            $chunkPos = strpos($string, '@@@');
        }

        // No more chunk keys, we send what is left.
        echo $string;
        ob_flush();
        flush();
    }

    /**
     * Replaces all chunk keys from a string with the original data.
     *
     * Saves the output to a file.
     *
     * @param string $string
     *   The chunked version of the output.
     */
    public function saveDechunkedToFile($string)
    {
        $this->cleanupOldChunks();

        // Cleanup old logfiles to prevent a overflow.
        $this->cleanupOldLogs($this->logDir);

        // Determine the filename.
        $timestamp = $this->fileService->fileStamp();
        $filename = $this->logDir . $timestamp . '.Krexx.html';
        $chunkPos = strpos($string, '@@@');

        while ($chunkPos !== false) {
            // We have a chunk, we save the html part.
            $this->fileService->putFileContents($filename, substr($string, 0, $chunkPos));

            $chunkPart = substr($string, $chunkPos);

            // We translate the first chunk.
            // Strangely, with a memory peak of 84MB, explode is
            // 2 mb cheaper than preg_match().
            $result = explode('@@@', $chunkPart, 3);
            $string = str_replace('@@@' . $result[1] . '@@@', $this->dechunkMe($result[1]), $chunkPart);
            $chunkPos = strpos($string, '@@@');
        }

        // No more chunks, we save what is left.
        $this->fileService->putFileContents($filename, $string);
        // Save our metadata, so a potential backend module can display it.
        // We may or may not have already some output for this file.
        if (!empty($this->metadata)) {
            $oldData = (array) json_decode($this->fileService->getFileContents($filename . '.json'), true);
            $mergedData = array_merge($oldData, $this->metadata);
            $this->fileService->putFileContents($filename . '.json', json_encode($mergedData), true);
            $this->metadata = array();
        }
    }

    /**
     * Deletes chunk files older then 1 hour, in case there are some left.
     */
    protected function cleanupOldChunks()
    {
        static $beenHere = false;

        // We only do this once.
        if (!$beenHere) {
            // Clean up leftover files.
            $chunkList = glob($this->chunkDir . '*.Krexx.tmp');
            if (!empty($chunkList)) {
                foreach ($chunkList as $file) {
                    // We delete everything that is older than one hour.
                    if ((filemtime($file) + 3600) < time()) {
                        unlink($file);
                    }
                }
            }
        }

        $beenHere = true;
    }

    /**
     * Deletes old logfiles.
     *
     * @param string $logDir
     *   The directory with the logfiles.
     */
    protected function cleanupOldLogs($logDir)
    {
        // Cleanup old logfiles to prevent a overflow.
        $logList = glob($logDir . "*.Krexx.html");
        if (!empty($logList)) {
            array_multisort(array_map('filemtime', $logList), SORT_DESC, $logList);
            $maxFileCount = (int)$this->pool->config->getSetting('maxfiles');
            $count = 1;
            // Cleanup logfiles.
            foreach ($logList as $file) {
                if ($count > $maxFileCount) {
                    if (is_writable($file)) {
                        unlink($file);
                    }
                    if (is_writable($file . '.json')) {
                        unlink($file . '.json');
                    }
                }
                $count++;
            }
        }
    }

    /**
     * Setter for the $useChunks.
     *
     * When the chunks folder is not writable, we will not use chunks.
     * This will increase the memory usage significantly!
     *
     * @param boolean $bool
     *   Are we using chunks?
     */
    public function setUseChunks($bool)
    {
        $this->useChunks = $bool;
    }

    /**
     * We add some metadata that we will store in a separate file.
     *
     * @param array $caller
     *   The caller from the caller finder.
     */
    public function addMetadata($caller)
    {
        $this->metadata[] = $caller;
    }
}
