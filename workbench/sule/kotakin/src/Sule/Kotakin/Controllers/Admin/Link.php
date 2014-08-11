<?php
namespace Sule\Kotakin\Controllers\Admin;

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
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\URL;

use Sule\Kotakin\Controllers\Admin\Base;

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
     * Show link list page
     *
     * @return Illuminate\View\View
     */
	public function index()
	{
        $linkList = $this->getKotakin()->getDocLink()->newQuery()
                            ->orderBy('created_at', 'desc')
                            ->get();

        $links = array();

        if (count($linkList) > 0) {
            $template = $this->getKotakin()->getTemplate()->getDocLink();
            foreach ($linkList as $index => $item) {
                if ($item->document) {
                    $links[$index] = $template->newInstance();
                    $links[$index]->setKotakin($this->getKotakin());
                    $links[$index]->setLink($item);
                } else {
                    $item->delete();
                }
            }
        }

        $this->getPage()->setActiveMenu('links');
        $this->getPage()->setAttribute('title', $this->getUtility()->t('Links | Admin'));

		return View::make('kotakin::admin_links', array(
			'page'  => $this->getPage(),
            'links' => $links
		));
	}

    /**
     * Archive page
     *
     * @param string $slug
     * @return Illuminate\Routing\Redirector
     */
    public function item($slug)
    {
        $action = Input::get('action', '');
        $link   = $this->getKotakin()->getDocLink()->newQuery()->newQuery()
                        ->where('slug', '=', $slug)
                        ->first();

        if ( ! $link)
            return Redirect::to('/admin/links');

        switch ($action) {
            case 'delete':
                    $link->delete();
                break;
        }

        Session::flash('success', $this->getUtility()->t('Link successfully deleted.'));

        return Redirect::to('/admin/links');
    }

}