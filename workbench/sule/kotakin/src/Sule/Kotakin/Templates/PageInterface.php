<?php
namespace Sule\Kotakin\Templates;

/*
 * This file is part of the Kotakin
 *
 * Author: Sulaeman <me@sulaeman.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Sule\Kotakin\Models\TermInterface as TermModelInterface;

interface PageInterface
{
    
    /**
     * Get current config value.
     *
     * @param string $key
     * @param string $formValue
     * @return string
     */
    public function config($key, $formValue = '');

    /**
     * Get the current active menu.
     *
     * @param string $menu
     * @return bool
     */
    public function isActiveMenu($menu);

    /**
     * Check if disk info is available.
     *
     * @return bool
     */
    public function diskInfoAvailable();

    /**
     * Return system disk free space.
     *
     * @param bool $useBytes
     * @return string | bool
     */
    public function diskFreeSpace($useBytes = false);

    /**
     * Return system disk capacity.
     *
     * @param bool $useBytes
     * @return string | bool
     */
    public function diskCapacity($useBytes = false);

    /**
     * Return system disk used space.
     *
     * @param bool $useBytes
     * @param bool $usePercentage
     * @return string
     */
    public function diskUsedSpace($useBytes = false, $usePercentage = false);

    /**
     * Return system max file upload size.
     *
     * @param bool $useBytes
     * @return string
     */
    public function maxFileUploadSize($useBytes = false);

    /**
     * Get current term model id.
     *
     * @return int
     */
    public function getCurrentFolderId();

    /**
     * Return list of breadcrumb items.
     *
     * @return string
     */
    public function breadcrumbs($itemFormat, $activeClass = 'active');

    /**
     * Check if in root collection.
     *
     * @return bool
     */
    public function isRootCollection();

    /**
     * Check if user allowed to upload in this collection.
     *
     * @return bool
     */
    public function isAllowUserUpload();

    /**
     * Return list of all terms.
     *
     * @return array
     */
    public function collection();

    /**
     * Return all available folder paths.
     *
     * @return array
     */
    public function paths();

    /**
     * Return available file types.
     *
     * @return array
     */
    public function fileTypes();

}
