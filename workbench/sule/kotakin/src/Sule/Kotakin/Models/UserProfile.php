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

use Sule\Kotakin\Models\UserProfileInterface;

class UserProfile extends Model implements UserProfileInterface
{

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'users_profile';

	/**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = array();

    /**
     * Returns the user profile's table name.
     *
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Returns the user profile's ID.
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->getKey();
    }

    /**
     * Saves the user profile.
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
     * Delete the user profile.
     *
     * @return bool
     */
    public function delete()
    {
        return parent::delete();
    }

    /**
     * Validates the user profile and throws a number of
     * Exceptions if validation fails.
     *
     * @return bool
     * @throws Sule\Kotakin\Models\InvalidDataException
     * @throws Sule\Kotakin\Models\UserProfileExistsException
     */
    public function validate()
    {
        // Check if user_id field was passed
        if ( ! $userId = $this->user_id)
            throw new InvalidDataException("A user_id is required for a user profile, none given.");

        // Check if file already exists
        $query = $this->newQuery();
        $persistedItem = $query->where('user_id', '=', $userId)->first();

        if ($persistedItem and $persistedItem->getId() != $this->getId())
            throw new UserProfileExistsException("A user profile already exists with user_id [$userId], must be unique for user profile.");

        return true;
    }

}