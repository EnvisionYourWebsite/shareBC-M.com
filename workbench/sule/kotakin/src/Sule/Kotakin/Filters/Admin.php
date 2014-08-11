<?php
namespace Sule\Kotakin\Filters;

/*
 * This file is part of the Kotakin
 *
 * Author: Sulaeman <me@sulaeman.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;

use Cartalyst\Sentry\Sentry;
use Cartalyst\Sentry\Users\UserInterface;
use Cartalyst\Sentry\Groups\GroupNotFoundException;

class Admin
{
    /**
     * The Sentry.
     *
     * @var Cartalyst\Sentry\Sentry
     */
    protected $sentry;

    /**
     * Create a new instance.
     *
     * @param Cartalyst\Sentry\Sentry
     * @return void
     */
    public function __construct(Sentry $sentry)
    {
        $this->sentry = $sentry;
    }

    /**
     * Get the sentry.
     *
     * @return Cartalyst\Sentry\Sentry
     */
    public function getSentry()
    {
        return $this->sentry;
    }

    /**
     * Check if user already logged in
     * then redirect to the previous page
     * if defined
     *
     * @return mixed
     */
    public function login()
    {
        if ($this->getSentry()->check()) {
            if ($this->inAllowedGroups($this->getSentry()->getUser())) {
                $continueUri = Input::get('continue');

                if (!empty($continueUri))
                    return Redirect::to($continueUri);
                else
                    return Redirect::to('/admin/dashboard');
            }
        }
    }

    /**
     * Check if user session expired
     * or not authenticated
     * then redirect to the login page
     * with current page URI info
     * if available
     *
     * @return mixed
     */
    public function auth()
    {
        $authed = true;

        if ( ! $this->getSentry()->check()) {
            $authed = false;
        } else {
            if ( ! $this->inAllowedGroups($this->getSentry()->getUser())) {
                $authed = false;
            }
        }

        if ( ! $authed) {
            return Redirect::to('/admin/login?continue='.urlencode(URL::current()));
        }
    }

    /**
     * Check if user is in allowed groups
     *
     * @param Cartalyst\Sentry\Users\UserInterface $user
     * @return bool
     */
    private function inAllowedGroups(UserInterface $user)
    {
        $allowed = false;

        // Check if in Super Admin group
        try {
            $group = $this->getSentry()->getGroupProvider()->findByName('Super Admin');

            if ($user->inGroup($group))
                $allowed = true;
        } catch (GroupNotFoundException $e) {}

        // Check if in Admin group
        if ( ! $allowed) {
            try {
                $group = $this->getSentry()->getGroupProvider()->findByName('Admin');

                if ($user->inGroup($group))
                    $allowed = true;
            } catch (GroupNotFoundException $e) {}
        }

        return $allowed;
    }
}
