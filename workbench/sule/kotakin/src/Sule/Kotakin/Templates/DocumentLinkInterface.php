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

interface DocumentLinkInterface
{

    /**
     * Return the link id.
     *
     * @return int
     */
    public function id();

    /**
     * Return the link slug.
     *
     * @return string
     */
    public function slug();

    /**
     * Return the link permalink.
     *
     * @return string
     */
    public function permalink();

    /**
     * Return the link hashed password.
     *
     * @return string
     */
    public function password();

    /**
     * Return the link limit.
     *
     * @return int
     */
    public function limit();

    /**
     * Return the link valid time.
     *
     * @return int
     */
    public function validUntil();

    /**
     * Return the link description.
     *
     * @return string
     */
    public function description();

    /**
     * Return the link total downloaded.
     *
     * @return int
     */
    public function downloadedTimes();

    /**
     * Return the link created time.
     *
     * @return int
     */
    public function createdAt();

    /**
     * Return the link updated time.
     *
     * @return int
     */
    public function updatedAt();

    /**
     * Return the link author.
     *
     * @return Sule\Kotakin\Templates\UserInterface
     */
    public function author();

    /**
     * Return the link document.
     *
     * @return Sule\Kotakin\Templates\DocumentInterface
     */
    public function doc();

    /**
     * Check if link unlocked.
     *
     * @return bool
     */
    public function unlocked();

    /**
     * Create a new instance of the given template.
     *
     * @return Sule\Kotakin\Templates\FileInterface|static
     */
    public function newInstance();

}
