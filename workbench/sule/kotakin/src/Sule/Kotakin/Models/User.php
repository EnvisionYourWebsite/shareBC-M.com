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

use Cartalyst\Sentry\Users\Eloquent\User as SentryUser;

class User extends SentryUser
{

	/**
	 * Returns the relationship between users and profile.
	 *
	 * @return Illuminate\Database\Eloquent\Relations\hasOne
	 */
	public function profile()
	{
		return $this->hasOne('Sule\Kotakin\Models\UserProfile');
	}

    /**
     * Returns the relationship between users and email recipients.
     *
     * @return Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function emailRecipients()
    {
        return $this->hasMany('Sule\Kotakin\Models\EmailRecipient', 'from_user_id');
    }

    /**
     * Returns the relationship between users and email recipients.
     *
     * @return Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function shares()
    {
        return $this->hasMany('Sule\Kotakin\Models\TermSharing');
    }

}
