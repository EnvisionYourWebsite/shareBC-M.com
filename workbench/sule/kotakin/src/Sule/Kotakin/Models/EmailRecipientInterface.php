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

interface EmailRecipientInterface
{

    /**
     * Returns the recipient's table name.
     *
     * @return string
     */
    public function getTable();

    /**
     * Returns the recipient's ID.
     *
     * @return mixed
     */
    public function getId();

    /**
     * Saves the recipient.
     *
     * @param  array  $options
     * @return bool
     */
    public function save(array $options = array());

    /**
     * Delete the recipient.
     *
     * @return bool
     */
    public function delete();

    /**
     * Validates the recipient and throws a number of
     * Exceptions if validation fails.
     *
     * @return bool
     * @throws Kotakin\Models\InvalidDataException
     * @throws Kotakin\Models\EmailRecipientExistsException
     */
    public function validate();

}
