<?php
namespace Sule\Kotakin\Composers;

/*
 * This file is part of the Kotakin
 *
 * Author: Sulaeman <me@sulaeman.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Illuminate\Support\Facades\Config;

use Sule\Kotakin\Kotakin;
use Cartalyst\Sentry\Sentry;

use Illuminate\View\View;

use Cartalyst\Sentry\Users\UserNotFoundException;

class DefaultViewData
{
    /**
     * The kotakin.
     *
     * @var Sule\Kotakin\Kotakin
     */
    protected $kotakin;

    /**
     * The Sentry.
     *
     * @var Cartalyst\Sentry\Sentry
     */
    protected $sentry;

    /**
     * Create a new instance.
     *
     * @param Sule\Kotakin\Kotakin  $kotakin
     * @param Cartalyst\Sentry\Sentry $sentry
     * @return void
     */
    public function __construct(Kotakin $kotakin, Sentry $sentry)
    {
        $this->kotakin = $kotakin;
        $this->sentry = $sentry;
    }

    /**
     * Get kotakin.
     *
     * @return Sule\Kotakin\Kotakin
     */
    public function getKotakin()
    {
        return $this->kotakin;
    }

    /**
     * Get Sentry.
     *
     * @return Cartalyst\Sentry\Sentry
     */
    public function getSentry()
    {
        return $this->sentry;
    }

    /**
     * Compose all needed view data
     *
     * @var Illuminate\View\View  $view
     * @return void
     */
    public function compose(View $view)
    {
        try {
            $user = $this->getSentry()->getUser();
        } catch (UserNotFoundException $e) {}

        if (isset($user)) {
            $userTemplate = $this->getKotakin()->getTemplate()->getUser();
            $userTemplate->setKotakin($this->getKotakin());
            $userTemplate->setSentry($this->getSentry());
            $userTemplate->setUser($user);
            $userTemplate->setAvailableMimeTypes(Config::get('kotakin::mime_types'));

            $view->with('currentUser', $userTemplate);

            unset($userTemplate);
            unset($user);
        }
    }
}
