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

use Cartalyst\Sentry\Users\UserInterface;

class Archive extends Base
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

        if ( ! $this->inSuperAdminGroup($sentry->getUser())) {
            App::abort(404);
        }
    }

    /**
     * Show archive list page
     *
     * @return Illuminate\View\View
     */
	public function index()
	{
        $archiveList = $this->getKotakin()->getMedia()->newQuery()
                            ->where('object_type', '=', 'Archive')
                            ->orderBy('created_at', 'desc')
                            ->get();

        $archives = array();

        if (count($archiveList) > 0) {
            $template = $this->getKotakin()->getTemplate()->getFile();
            foreach ($archiveList as $index => $item) {
                $archives[$index] = $template->newInstance();
                $archives[$index]->setKotakin($this->getKotakin());
                $archives[$index]->setFile($item);
            }
        }

        $this->getPage()->setActiveMenu('preference');
        $this->getPage()->setAttribute('title', $this->getUtility()->t('Archives | Admin'));

		return View::make('kotakin::admin_archives', array(
			'page'     => $this->getPage(),
            'archives' => $archives
		));
	}

    /**
     * Archive page
     *
     * @param int $id
     * @return Illuminate\Routing\Redirector
     */
    public function item($id)
    {
        $action = Input::get('action', '');
        $media = $this->getKotakin()->getMedia()->newQuery()
                        ->where('id', '=', $id)
                        ->first();

        if ( ! $media)
            return Redirect::to('/admin/preference/archive');

        switch ($action) {
            case 'delete':
                    @unlink(storage_path().'/'.$media->getAttribute('path').'/'.$media->getAttribute('filename').'.'.$media->getAttribute('extension'));
                    $media->delete();
                break;
        }

        Session::flash('success', $this->getUtility()->t('Archive successfully deleted.'));

        return Redirect::to('/admin/preference/archive');
    }

    /**
     * Check if user is in super admin group
     *
     * @param Cartalyst\Sentry\Users\UserInterface $user
     * @return bool
     */
    protected function inSuperAdminGroup(UserInterface $user)
    {
        $allowed = false;

        try {
            $group = $this->getSentry()->getGroupProvider()->findByName('Super Admin');

            if ($user->inGroup($group))
                $allowed = true;

            unset($group);
        } catch (GroupNotFoundException $e) {}

        return $allowed;
    }

}