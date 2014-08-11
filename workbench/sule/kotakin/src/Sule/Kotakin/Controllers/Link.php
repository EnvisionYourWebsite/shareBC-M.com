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
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\URL;

use Sule\Kotakin\Controllers\Base;

use Sule\Kotakin\Kotakin;
use Cartalyst\Sentry\Sentry;

class Link extends Base
{
    /**
     * Create a new instance.
     *
     * @param Sule\Kotakin\Kotakin
     * @param Cartalyst\Sentry\Sentry $sentry
     * @return void
     */
    public function __construct(Kotakin $kotakin, Sentry $sentry)
    {
        parent::__construct($kotakin, $sentry);
    }

    /**
     * Show link page
     *
     * @param string $slug
     * @return Illuminate\View\View
     */
	public function index($slug)
	{
        $docLink = $this->getKotakin()->getDocLink()->newQuery()
                        ->where('slug', '=', $slug)
                        ->first();

        if ( ! $docLink)
            App::abort(404);

        if ($_POST) {
            $password = Input::get('password', '');
            Session::put('link_pass', $password);

            return Redirect::to(URL::current());
        } else {
            $password = Session::get('link_pass');
        }

        $download = (bool) Input::get('dl', 0);

        $user = $this->getSentry()->getUserProvider()->getEmptyUser();

        $link = $this->getKotakin()->getTemplate()->getDocLink();
        $link->setKotakin($this->getKotakin());
        $link->setUser($user);
        $link->setLink($docLink);
        $link->setPassword($password);

        $isLimitReached = ($link->downloadedTimes() >= $link->limit() and $link->limit() > 0);
        $timeStillValid = true;
        
        if ($link->validUntil() > 0 and time() > $link->validUntil())
            $timeStillValid = false;
        
        if ( ! $timeStillValid)
            App::abort(404);

        if ($download and $link->unlocked() and ! $isLimitReached) {
            if ($docLink->document) {
                if ($docLink->document->media) {
                    $file  = storage_path().'/'.$docLink->document->media->getAttribute('path').'/'.$docLink->document->media->getAttribute('filename').'.'.$docLink->document->media->getAttribute('extension');
                    $title = $docLink->document->media->getAttribute('title');

                    $docLink->setAttribute('downloaded_times', $docLink->getAttribute('downloaded_times') + 1);
                    if ($docLink->save())
                        return Response::download($file, $title, array(
                            'Content-type' => $docLink->document->media->getAttribute('mime_type')
                        ));
                }
            }

            App::abort(404);
        }

        $this->getPage()->setActiveMenu('links');
        $this->getPage()->setAttribute('title', $link->doc()->file()->title());

		return View::make('kotakin::link', array(
			'page' => $this->getPage(),
            'link' => $link
		));
	}

}