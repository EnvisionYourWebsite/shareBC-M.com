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

use Illuminate\Support\Facades\View;

use Sule\Kotakin\Controllers\Base;
use Sule\Kotakin\Kotakin;
use Cartalyst\Sentry\Sentry;

class Error extends Base
{

	/**
     * Create a new instance.
     *
     * @param Sule\Kotakin\Kotakin $kotakin
     * @param Cartalyst\Sentry\Sentry $sentry
     * @return void
     */
    public function __construct(Kotakin $kotakin, Sentry $sentry)
    {
        parent::__construct($kotakin, $sentry);
    }

    /**
     * Show 404 error page.
     *
     * @return Illuminate\View\View
     */
    public function _404()
    {
        $this->getPage()->setAttribute('title', $this->getUtility()->t('Page Not Found'));

        return View::make('kotakin::error_404', array(
            'page'             => $this->getPage(),
            'noLoadSupersized' => true
        ));
    }

}