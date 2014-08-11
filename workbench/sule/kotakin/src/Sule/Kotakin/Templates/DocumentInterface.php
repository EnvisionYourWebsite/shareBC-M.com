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

interface DocumentInterface
{
    
    /**
     * Return document id.
     *
     * @return int
     */
    public function id();

    /**
     * Return document basename.
     *
     * @return string
     */
    public function baseName();

    /**
     * Return document name.
     *
     * @return string
     */
    public function name();

    /**
     * Return document slug.
     *
     * @return string
     */
    public function slug();

    /**
     * Return document permalink.
     *
     * @return string
     */
    public function permalink();

    /**
     * Return document download permalink.
     *
     * @return string
     */
    public function downloadPermalink();

    /**
     * Return document description.
     *
     * @return string
     */
    public function description();

    /**
     * Return document mime type.
     *
     * @return string
     */
    public function kind();

    /**
     * Return document created time.
     *
     * @return int
     */
    public function createdAt();

    /**
     * Return document updated time.
     *
     * @return int
     */
    public function updatedAt();

    /**
     * Return document location permalink.
     *
     * @return string
     */
    public function locationPermalink();

    /**
     * Return document location.
     *
     * @return string
     */
    public function location();

    /**
     * Return document file.
     *
     * @return Sule\Kotakin\Templates\FileInterface
     */
    public function file();

    /**
     * Create a new instance of the given template.
     *
     * @return Sule\Kotakin\Templates\FileInterface|static
     */
    public function newInstance();

}
