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

use Illuminate\Routing\Controllers\Controller;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Config;

use Sule\Kotakin\Kotakin;
use Cartalyst\Sentry\Sentry;
use Sule\Kotakin\Libraries\TwigEx;

use Cartalyst\Sentry\Users\UserNotFoundException;

class Base extends Controller
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
     * The utility.
     *
     * @var Sule\Kotakin\Libraries\Utility
     */
    protected $utility;

    /**
     * The page template.
     *
     * @var Sule\Kotakin\Templates\PageInterface
     */
    protected $page;

	/**
     * Create a new instance.
     *
     * @param Sule\Kotakin\Kotakin
     * @param Cartalyst\Sentry\Sentry $sentry
     * @return void
     */
    public function __construct(Kotakin $kotakin, Sentry $sentry)
    {
        $this->kotakin = $kotakin;
        $this->sentry  = $sentry;
        $this->utility = $kotakin->getUtility();
        $this->page    = $kotakin->getTemplate();

        $this->page->setKotakin($kotakin);
        $this->page->setFileTypes(Config::get('kotakin::mime_types'));

        Event::listen('twigbridge.twig', function($twig) use ($kotakin) {
            $extension = new TwigEx(new Application, $twig);
            $extension->setUtility($kotakin->getUtility());
            $twig->addExtension($extension);
        });

        $this->setDefaultPageAttributes();
        $this->setDefaultJSVars();
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
     * Get the sentry.
     *
     * @return Cartalyst\Sentry\Sentry
     */
    protected function getSentry()
    {
        return $this->sentry;
    }

    /**
     * Get utility.
     *
     * @return Sule\Kotakin\Libraries\Utility
     */
    public function getUtility()
    {
        return $this->utility;
    }

    /**
     * Get page template.
     *
     * @return Sule\Kotakin\Templates\PageInterface
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Set page default attributes.
     *
     * @return void
     */
    protected function setDefaultPageAttributes()
    {
        $this->getPage()->setAttribute('brand', $this->getKotakin()->config('brand'));
    }

    /**
     * Set default JavaScript variables.
     *
     * @return void
     */
    protected function setDefaultJSVars()
    {
        $user = null;

        $allowedFileExts = array();
        $dateFormat      = 'Y/m/d';

        try {
            $user = $this->getSentry()->getUser();
        } catch (UserNotFoundException $e) {}

        if ($user) {
            $allowedFileExts = explode(',', $user->profile->getAttribute('allowed_file_types'));
            $dateFormat      = $user->profile->getAttribute('date_format');
        }

        $js = '<script type="text/javascript">';
        $js .= 'var baseUrl = "'.URL::to('/').'";';
        $js .= 'var csrfToken = "'.csrf_token().'";';
        $js .= 'var errorAJAXMessage = "'.$this->getUtility()->t('There was problem contacting our server, please try reload the page.').'";';
        $js .= 'var allowedFileExts = "'.implode('|', $allowedFileExts).'";';
        $js .= 'var availablePaths = '.json_encode($this->getPage()->paths()).';';
        $js .= 'var dateFormat = "'.$dateFormat.'";';
        $js .= '</script>';

        $this->getPage()->setMetadata($js);

        unset($user);
    }

}