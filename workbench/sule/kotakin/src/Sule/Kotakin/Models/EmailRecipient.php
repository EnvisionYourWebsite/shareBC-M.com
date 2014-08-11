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

use Illuminate\Database\Eloquent\Model;

use Sule\Kotakin\Models\EmailRecipientInterface;

class EmailRecipient extends Model implements EmailRecipientInterface
{

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'email_recipients';

	/**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = array();

    /**
     * Returns the recipient's table name.
     *
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Returns the recipient's ID.
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->getKey();
    }

    /**
     * Saves the recipient.
     *
     * @param  array  $options
     * @return bool
     */
    public function save(array $options = array())
    {
        $this->validate();
        return parent::save();
    }

    /**
     * Delete the recipient.
     *
     * @return bool
     */
    public function delete()
    {
        return parent::delete();
    }

    /**
     * Validates the recipient and throws a number of
     * Exceptions if validation fails.
     *
     * @return bool
     * @throws Sule\Kotakin\Models\InvalidDataException
     * @throws Sule\Kotakin\Models\EmailRecipientExistsException
     */
    public function validate()
    {
        // Check if from_user_id field was passed
        if ( ! $this->from_user_id)
            throw new InvalidDataException("A from_user_id is required for a recipient, none given.");

        // Check if to_user_id field was passed
        if ( ! $this->to_user_id)
            throw new InvalidDataException("A to_user_id is required for a recipient, none given.");

        return true;
    }

}