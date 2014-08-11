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

interface FolderInterface
{

    /**
     * Returns the folder's table name.
     *
     * @return string
     */
    public function getTable();

    /**
     * Returns the folder's ID.
     *
     * @return mixed
     */
    public function getId();

    /**
     * Saves the folder.
     *
     * @param  array  $options
     * @return bool
     */
    public function save(array $options = array());

    /**
     * Delete the folder.
     *
     * @return bool
     */
    public function delete();

    /**
     * Validates the folder and throws a number of
     * Exceptions if validation fails.
     *
     * @return bool
     * @throws Sule\Kotakin\Models\InvalidDataException
     * @throws Sule\Kotakin\Models\FolderExistsException
     */
    public function validate();

}
