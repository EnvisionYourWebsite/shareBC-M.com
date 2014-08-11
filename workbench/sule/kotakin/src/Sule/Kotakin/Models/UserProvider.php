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

use Cartalyst\Sentry\Hashing\HasherInterface;
use Cartalyst\Sentry\Users\Eloquent\Provider;

use Cartalyst\Sentry\Users\UserNotFoundException;

class UserProvider extends Provider
{

	/**
	 * The Eloquent user profile model.
	 *
	 * @var string
	 */
	protected $profileModel = 'Sule\Kotakin\Models\UserProfile';

	/**
	 * Create a new Eloquent User provider.
	 *
	 * @param  Cartalyst\Sentry\Hashing\HasherInterface  $hasher
	 * @param  string  $model
	 * @return void
	 */
	public function __construct(HasherInterface $hasher, $model = null)
	{
		parent::__construct($hasher, $model);
	}

	/**
	 * Finds a user by the url slug value.
	 *
	 * @param  string  $slug
	 * @return Cartalyst\Sentry\Users\UserInterface
	 * @throws Cartalyst\Sentry\Users\UserNotFoundException
	 */
	public function findByUrlSlug($slug)
	{
		$model = $this->createModel();

		if ( ! $user = $model->newQuery()->where('url_slug', '=', $slug)->first()) {
			throw new UserNotFoundException("A user could not be found with a slug value of [$slug].");
		}

		return $user;
	}

	/**
	 * Returns an empty user profile object.
	 *
	 * @return Sule\Kotakin\Models\UserProfileInterface
	 */
	public function getEmptyUserProfile()
	{
		return $this->createProfileModel();
	}

	/**
	 * Create a new instance of the profile model.
	 *
	 * @return Illuminate\Database\Eloquent\Model
	 */
	public function createProfileModel()
	{
		$class = '\\'.ltrim($this->profileModel, '\\');

		return new $class;
	}

	/**
	 * Sets a new profile model class name to be used at
	 * runtime.
	 *
	 * @param  string  $model
	 */
	public function setProfileModel($model)
	{
		$this->profileModel = $model;
	}

}
