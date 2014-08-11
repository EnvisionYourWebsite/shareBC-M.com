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

interface FolderInterface
{
    
    /**
     * Return folder id.
     *
     * @return int
     */
    public function id();

    /**
     * Return folder parent id.
     *
     * @return int
     */
    public function parentId();

    /**
     * Return folder type.
     *
     * @return string
     */
    public function type();

    /**
     * Return folder slug.
     *
     * @return string
     */
    public function slug();

    /**
     * Return folder permalink.
     *
     * @return string
     */
    public function permalink();

    /**
     * Return folder download permalink.
     *
     * @return string
     */
    public function downloadPermalink();

    /**
     * Return folder base name.
     *
     * @return string
     */
    public function baseName();

    /**
     * Return folder name.
     *
     * @return string
     */
    public function name();

    /**
     * Return folder password.
     *
     * @return string
     */
    public function password();

    /**
     * Return folder description.
     *
     * @return string
     */
    public function description();

    /**
     * Return folder kind (shared or not).
     *
     * @return string
     */
    public function kind();

    /**
     * Check is folder shared.
     *
     * @return bool
     */
    public function isShared();

    /**
     * Check is use allowed to upload in this folder.
     *
     * @return bool
     */
    public function isAllowUserUpload();

    /**
     * Return folder created time.
     *
     * @return int
     */
    public function createdAt();

    /**
     * Return folder updated time.
     *
     * @return int
     */
    public function updatedAt();

    /**
     * Create a new instance of the given template.
     *
     * @return Sule\Kotakin\Templates\FileInterface|static
     */
    public function newInstance();

}
