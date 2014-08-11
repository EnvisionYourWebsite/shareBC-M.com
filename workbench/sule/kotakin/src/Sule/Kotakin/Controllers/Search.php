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
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\URL;

use Sule\Kotakin\Controllers\BaseUser;

use Sule\Kotakin\Kotakin;
use Cartalyst\Sentry\Sentry;

class Search extends BaseUser
{
    
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
        parent::__construct($kotakin, $sentry, $slug);
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
            return Redirect::to('/'.$this->getSlug());

        $termModel = $this->getKotakin()->getTerm();
        $termSharingModel = $this->getKotakin()->getTermSharing();
        $terms = $termModel->newQuery()
                        ->join(
                            $termSharingModel->getTable(), 
                            $termModel->getTable().'.id', 
                            '=', 
                            $termSharingModel->getTable().'.term_id'
                        )
                        ->where($termModel->getTable().'.name', 'like', '%'.$keywords.'%')
                        ->where($termSharingModel->getTable().'.user_id', '=', $this->getUser()->getId())
                        ->orderBy($termModel->getTable().'.is_folder', 'desc')
                        ->get();

        $total = count($terms);

        if ($total > 0) {
            $template = $this->getKotakin()->getTemplate()->getTerm();

            foreach ($terms as $index => $term) {
                $result[$index] = $template->newInstance();
                $result[$index]->setKotakin($this->getKotakin());
                $result[$index]->setTerm($term);
                $result[$index]->setForUser($this->getUser());
            }
        }

        unset($terms);

        $this->getPage()->setActiveMenu('dashboard');

        $this->getPage()->setAttribute('title', sprintf($this->getUtility()->t('Search | %s'), $this->getPage()->getAttribute('brand')));

        return View::make('kotakin::user_search', array(
            'page'     => $this->getPage(),
            'total'    => $total,
            'keywords' => $keywords,
            'result'   => $result
        ));
    }

}