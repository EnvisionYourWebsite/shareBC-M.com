<?php
namespace Sule\Kotakin\Models;

/*
 * This file is part of the Kotakin
 *
 * Author: Sulaeman <me@sulaeman.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

interface MediaInterface
{

    /**
     * Returns the media's table name.
     *
     * @return string
     */
    public function getTable();

    /**
     * Returns the media's ID.
     *
     * @return mixed
     */
    public function getId();

    /**
     * Saves the media.
     *
     * @param  array  $options
     * @return bool
     */
    public function save(array $options = array());

    /**
     * Delete the media.
     *
     * @return bool
     */
    public function delete();

    /**
     * Validates the media and throws a number of
     * Exceptions if validation fails.
     *
     * @return bool
     * @throws Kotakin\Models\InvalidDataException
     * @throws Kotakin\Models\MediaExistsException
     */
    public function validate();

}
