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

interface UserProfileInterface
{

    /**
     * Returns the user profile's table name.
     *
     * @return string
     */
    public function getTable();

    /**
     * Returns the user profile's ID.
     *
     * @return mixed
     */
    public function getId();

    /**
     * Saves the user profile.
     *
     * @param  array  $options
     * @return bool
     */
    public function save(array $options = array());

    /**
     * Delete the user profile.
     *
     * @return bool
     */
    public function delete();

    /**
     * Validates the user profile and throws a number of
     * Exceptions if validation fails.
     *
     * @return bool
     * @throws Sule\Kotakin\Models\InvalidDataException
     * @throws Sule\Kotakin\Models\UserProfileExistsException
     */
    public function validate();

}
