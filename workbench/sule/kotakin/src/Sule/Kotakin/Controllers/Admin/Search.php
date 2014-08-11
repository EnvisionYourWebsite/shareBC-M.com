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
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\URL;

use Sule\Kotakin\Controllers\Admin\Base;

use Sule\Kotakin\Kotakin;
use Cartalyst\Sentry\Sentry;

class Search extends Base
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

        $userList = $sentry->getUserProvider()->findAll();
        $users    = array();

        if (count($userList) > 0) {
            $template = $this->getKotakin()->getTemplate()->getUser();
            foreach ($userList as $index => $user) {
                $users[$index] = $template->newInstance();
                $users[$index]->setKotakin($this->getKotakin());
                $users[$index]->setUser($user);
            }
        }

        $this->getPage()->setUsers($users);

        unset($users);
    }

    /**
     * Show search page
     *
     * @return Illuminate\View\View
     */
    public function index()
    {
        $keywords = $this->getUtility()->xssClean(Input::get('q', ''));
        $result = array();

        if (empty($keywords))
            return Redirect::to('/admin');

        $terms = $this->getKotakin()->getTerm()
                        ->where('name', 'like', '%'.$keywords.'%')
                        ->orderBy('is_folder', 'desc')
                        ->get();

        $total = count($terms);

        if ($total > 0) {
            $template = $this->getKotakin()->getTemplate()->getTerm();

            foreach ($terms as $index => $term) {
                $result[$index] = $template->newInstance();
                $result[$index]->setKotakin($this->getKotakin());
                $result[$index]->setTerm($term);
            }
        }

        unset($terms);

        $this->getPage()->setActiveMenu('dashboard');

        $this->getPage()->setAttribute('title', $this->getUtility()->t('Search | Admin'));

        return View::make('kotakin::admin_search', array(
            'page'     => $this->getPage(),
            'total'    => $total,
            'keywords' => $keywords,
            'result'   => $result
        ));
    }

}