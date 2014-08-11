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

interface EmailTemplateInterface
{

    /**
     * Returns the email's table name.
     *
     * @return string
     */
    public function getTable();

    /**
     * Returns the email's ID.
     *
     * @return mixed
     */
    public function getId();

    /**
     * Saves the email.
     *
     * @param  array  $options
     * @return bool
     */
    public function save(array $options = array());

    /**
     * Delete the email.
     *
     * @return bool
     */
    public function delete();

    /**
     * Validates the email and throws a number of
     * Exceptions if validation fails.
     *
     * @return bool
     * @throws Kotakin\Models\InvalidDataException
     * @throws Kotakin\Models\EmailTemplateExistsException
     */
    public function validate();

}
