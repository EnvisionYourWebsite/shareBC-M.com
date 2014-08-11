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

interface OptionInterface
{

    /**
     * Returns the option's table name.
     *
     * @return string
     */
    public function getTable();

    /**
     * Returns the option's ID.
     *
     * @return mixed
     */
    public function getId();

    /**
     * Saves the option.
     *
     * @param  array  $options
     * @return bool
     */
    public function save(array $options = array());

    /**
     * Delete the option.
     *
     * @return bool
     */
    public function delete();

    /**
     * Validates the option and throws a number of
     * Exceptions if validation fails.
     *
     * @return bool
     * @throws Kotakin\Models\InvalidDataException
     * @throws Kotakin\Models\OptionExistsException
     */
    public function validate();

}
