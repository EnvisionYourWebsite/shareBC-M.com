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
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

use Sule\Kotakin\Controllers\BaseUser;
use Sule\Kotakin\Kotakin;
use Cartalyst\Sentry\Sentry;

use Sule\Kotakin\Controllers\Admin\Folder;
use Sule\Kotakin\Controllers\Document;
use Sule\Kotakin\Templates\TermInterface;
use Sule\Kotakin\Models\TermInterface as TermModelInterface;

use Cartalyst\Sentry\Users\UserNotFoundException;

class Dashboard extends BaseUser
{

    /**
     * Current folder
     *
     * @var Sule\Kotakin\Models\FolderInterface
     */
    protected $folder;

    /**
     * Current term
     *
     * @var Sule\Kotakin\Models\TermInterface
     */
    protected $term;

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
     * Show the dahsboard page.
     *
     * @param string slug
     * @return Illuminate\View\View
     */
	public function index($slug = '')
	{
        $this->getPage()->setForUser($this->getUser());
        $this->getPage()->setAttribute('title', sprintf($this->getUtility()->t('Dashboard | %s'), $this->getPage()->getAttribute('brand')));

        if ( ! empty($slug)) {
            $download = Input::get('dl', 0);
            $slug     = $this->getUtility()->xssClean($slug);

            $this->folder = $this->getKotakin()->getFolder()->newQuery()
                            ->where('slug', '=', $slug)->first();

            if ( ! is_object($this->folder))
                App::abort(404);

            $this->term = $this->getKotakin()->getTerm()->newQuery()
                        ->where('object_id', '=', $this->folder->getId())
                        ->where('is_folder', '=', 1)
                        ->first();

            if ( ! is_object($this->term))
                App::abort(404);

            if ( ! $this->isFolderSharedToThisUser($this->term)) {
                Session::flash('error', sprintf($this->getUtility()->t('Suddenly your access to the "%s" folder is removed by Administrator.'), $this->term->getAttribute('name')));

                $parent = $this->term->parent;

                if ($parent) {
                    $folder = $this->getKotakin()->getFolder()->newQuery()
                                    ->where('id', '=', $parent->getAttribute('object_id'))
                                    ->first();

                    if ($folder) {
                        return Redirect::to('/'.$this->getSlug().'/folder/'.$folder->getAttribute('slug'));
                    }
                }

                Redirect::to('/'.$this->getSlug());
            }

            if ( ! $_POST) {
                $termTemplate = $this->getKotakin()->getTemplate()->getTerm()->newInstance();
                $termTemplate->setKotakin($this->getKotakin());
                $termTemplate->setTerm($this->term);

                $breadcrumbs = array($termTemplate);

                unset($termTemplate);

                $parentId = $this->term->getAttribute('parent_id');
                if ( ! empty($parentId)) {
                    $breadcrumbs = $this->breadcrumbs($parentId, $breadcrumbs);
                }

                krsort($breadcrumbs);
                $breadcrumbs = array_values($breadcrumbs);

                $this->getPage()->setCollectionBreadcrumbs($breadcrumbs);

                unset($breadcrumbs);

                $this->getPage()->setCollectionParentTerm($this->term);
                $this->getPage()->setAttribute('title', sprintf($this->getUtility()->t('Dashboard | %s'), $this->getPage()->getAttribute('brand')));
            }

            if ($download) {
                $folder = new Folder($this->getKotakin(), $this->getSentry());
                return $folder->download($this->folder, $this->term);
            }
        }

        if ($_POST) {
            return $this->proccessPost();
        }

		return $this->show();
	}

    /**
     * Notifying all email recipients regarding new uploaded files
     *
     * @return Illuminate\Routing\Response
     */
    public function notify()
    {
        if (Request::ajax()) {
            $recipients = $this->getUser()->emailRecipients;
            $tos = array();

            if (count($recipients) > 0) {
                foreach ($recipients as $index => $recipient) {
                    try {
                        $user = $this->getSentry()->getUserProvider()->findById($recipient->getAttribute('to_user_id'));

                        $tos[$index] = $this->getKotakin()->getTemplate()->getUser()->newInstance();
                        $tos[$index]->setKotakin($this->getKotakin());
                        $tos[$index]->setUser($user);
                    } catch (UserNotFoundException $e) {}
                }
            }

            if ( ! empty($tos)) {
                $terms = Input::get('files', '');
                $terms = explode(',', substr($terms, 1, strlen($terms)));

                if ( ! empty($terms)) {
                    $files = array();
                    $index = 0;

                    foreach ($terms as $termId) {
                        $term = $this->getKotakin()->getTerm()->find($termId);

                        if ($term) {
                            $doc = $this->getKotakin()->getDoc()->find($term->getAttribute('object_id'));

                            if ($doc) {
                                $media = $doc->media;

                                if ($media) {
                                    $files[$index] = $this->getKotakin()->getTemplate()->getFile()->newInstance();
                                    $files[$index]->setKotakin($this->getKotakin());
                                    $files[$index]->setFile($media);

                                    ++$index;
                                }

                                unset($media);
                            }

                            unset($doc);
                        }

                        unset($term);
                    }

                    if ( ! empty($files)) {
                        $userTemplate = $this->getKotakin()->getTemplate()->getUser()->newInstance();
                        $userTemplate->setKotakin($this->getKotakin());
                        $userTemplate->setUser($user);

                        foreach ($tos as $to) {
                            $this->getKotakin()->getMailer()->send('new_files_uploaded', array(
                                'page'          => $this->getPage(),
                                'user'          => $userTemplate,
                                'recipientUser' => $to,
                                'files'         => $files
                            ), function($message) use ($to) {
                                $message->to($to->email(), $to->firstName());
                            });
                        }
                    }

                    unset($files);
                }

                unset($terms);
            }

            unset($tos);
            unset($recipients);

            return Response::json(array('success' => 1));
        }
    }

    /**
     * Processing POST data
     *
     * @return Illuminate\Routing\Redirector
     */
    protected function proccessPost()
    {
        if (Input::has('_new_folder')) {
            if ($this->isFolderSharedToThisUser($this->term)) {
                $folder = new Folder($this->getKotakin(), $this->getSentry());
                return $folder->create($this->folder, $this->term);
            } else {
                Session::flash('error', $this->getUtility()->t('Suddenly your access to the parent folder is removed by Administrator.'));
                return Redirect::to(URL::current());
            }
        }

        $action = $this->getUtility()->xssClean(Input::get('_action', ''));

        if ( ! empty($action)) {
            $itemId = (int) Input::get('item', 0);

            if (empty($itemId))
                App::abort(404);

            $term = $this->getKotakin()->getTerm()->newQuery()
                        ->where('id', '=', $itemId)->first();

            if ( ! $term)
                App::abort(404);

            switch ($action) {
                case 'rename':
                        return $this->renaming($term, Input::get('name', ''));
                    break;
                
                case 'delete':
                        return $this->deleting($term);
                    break;
            }
        }

        App::abort(404);
    }

    /**
     * sharing folder.
     *
     * @param Sule\Kotakin\Models\TermInterface $term
     * @param array $users
     * @return Illuminate\Routing\Redirector
     */
    protected function sharing(TermModelInterface $term, Array $users)
    {
        $folder = new Folder($this->getKotakin(), $this->getSentry());
        return $folder->share($term, $users);
    }

    /**
     * Renaming term.
     *
     * @param Sule\Kotakin\Models\TermInterface $term
     * @param string $name
     * @return Illuminate\Routing\Redirector
     */
    protected function renaming(TermModelInterface $term, $name)
    {
        if ($term->getAttribute('is_folder') == 1) {
            $folder = new Folder($this->getKotakin(), $this->getSentry(), null, null, $this->getSlug());
            return $folder->rename($term, $name);
        } else {
            $doc = new Document($this->getKotakin(), $this->getSentry(), null, null, $this->getSlug());
            return $doc->rename($term, $name);
        }
    }

    /**
     * Deleting term.
     *
     * @param Sule\Kotakin\Models\TermInterface $term
     * @return Illuminate\Routing\Redirector
     */
    protected function deleting(TermModelInterface $term)
    {
        if ($term->getAttribute('is_folder') == 1) {
            $folder = new Folder($this->getKotakin(), $this->getSentry());
            return $folder->delete($term);
        } else {
            $doc = new Document($this->getKotakin(), $this->getSentry(), null, null, $this->getSlug());

            $theDoc = $this->getKotakin()->getDoc()->newQuery()
                            ->where('id', '=', $term->getAttribute('object_id'))->first();

            if ($theDoc) {
                $doc->delete($theDoc->getAttribute('slug'));
            } else {
                $doc->delete();
                $term->delete();
            }
        }

        return Redirect::to(URL::current());
    }

    /**
     * Show the dahsboard view.
     *
     * @return Illuminate\View\View
     */
    protected function show()
    {
        $this->getPage()->setActiveMenu('dashboard');

        return View::make('kotakin::user_dashboard', array(
            'page' => $this->getPage()
        ));
    }

    /**
     * Create breadcrumbs
     *
     * @param int $objectId
     * @param array $items
     * @return array
     */
    protected function breadcrumbs($objectId, $items = array())
    {
        $term = $this->getKotakin()->getTerm()->newQuery()
                    ->where('id', '=', $objectId)
                    ->where('is_folder', '=', 1)
                    ->first();

        $termTemplate = $this->getKotakin()->getTemplate()->getTerm()->newInstance();
        $termTemplate->setKotakin($this->getKotakin());
        $termTemplate->setTerm($term);

        $items[] = $termTemplate;

        $parentId = $term->getAttribute('parent_id');
        if ( ! empty($parentId)) {
            $items = $this->breadcrumbs($parentId, $items);
        }

        unset($termTemplate);
        unset($term);

        return $items;
    }

    /**
     * Check if term shared to this user
     *
     * @param Sule\Kotakin\Models\TermInterface $term
     * @return bool
     */
    protected function isFolderSharedToThisUser(TermModelInterface $term)
    {
        $shares = $term->shares;
        $isShared = false;

        if (count($shares) > 0) {
            foreach ($shares as $item) {
                if ($item->getAttribute('user_id') == $this->getUser()->getId()) {
                    $isShared = true;
                    break;
                }
            }
        }

        unset($shares);

        return $isShared;
    }

}