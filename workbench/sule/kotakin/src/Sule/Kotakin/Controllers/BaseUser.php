<?php
namespace Sule\Kotakin\Controllers;

/*
 * This file is part of the Kotakin
 *
 * Author: Sulaeman <me@sulaeman.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Illuminate\Support\Facades\App;

use Sule\Kotakin\Controllers\Base as BaseController;
use Sule\Kotakin\Kotakin;
use Cartalyst\Sentry\Sentry;

use Cartalyst\Sentry\Users\UserNotFoundException;

class BaseUser extends BaseController
{

    /**
     * The current user slug.
     *
     * @var string
     */
    protected $slug;

    /**
     * The current user.
     *
     * @var Cartalyst\Sentry\Users\UserInterface
     */
    protected $user;

    /**
     * Create a new instance.
     *
     * @param Sule\Kotakin\Kotakin $kotakin
     * @param Cartalyst\Sentry\Sentry $sentry
     * @param string $slug
     * @return void
     */
    public function __construct(Kotakin $kotakin, Sentry $sentry, $slug)
    {
    	parent::__construct($kotakin, $sentry);

        $this->slug = $slug;

        try {
            $this->user = $this->getSentry()->getUserProvider()->findByUrlSlug($slug);
        } catch (UserNotFoundException $e) {
            App::abort(404);
        }

        $this->getPage()->setUserSlug($slug);
        $this->setUserJSVars();
    }

    /**
     * Get current user slug.
     *
     * @return string
     */
    protected function getSlug()
    {
        return $this->slug;
    }

    /**
     * Get current user.
     *
     * @return Cartalyst\Sentry\Users\UserInterface
     */
    protected function getUser()
    {
        return $this->user;
    }

    /**
     * Set default JavaScript variables.
     *
     * @return void
     */
    protected function setUserJSVars()
    {
        $js = '<script type="text/javascript">';
        $js .= 'var userSlug = "'.$this->getSlug().'";';
        $js .= '</script>';

        $this->getPage()->setMetadata($js);
    }

}