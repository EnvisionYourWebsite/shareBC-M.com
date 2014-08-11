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

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

use Sule\Kotakin\Controllers\Admin\Base;
use Sule\Kotakin\Kotakin;
use Cartalyst\Sentry\Sentry;
use Thapp\JitImage\JitImage;

use Swift_Attachment;

use Sule\Kotakin\Controllers\Admin\Folder;
use Sule\Kotakin\Templates\TermInterface;
use Sule\Kotakin\Models\TermInterface as TermModelInterface;

use Sule\Kotakin\Models\FolderExistsException;

class Dashboard extends Base
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
     * @return void
     */
    public function __construct(Kotakin $kotakin, Sentry $sentry, JitImage $jitImage)
    {
    	parent::__construct($kotakin, $sentry);

        $this->jitImage = $jitImage;
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
     * Show the dahsboard page.
     *
     * @param string slug
     * @return Illuminate\View\View
     */
	public function index($slug = '')
	{
        // Run DB update
        Artisan::call('migrate', array('--bench' => 'sule/kotakin'));

        $this->getPage()->setAttribute('title', $this->getUtility()->t('Dashboard | Admin'));

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
                $this->getPage()->setAttribute('title', $this->getUtility()->t('Dashboard | Admin'));
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
     * Processing POST data
     *
     * @return Illuminate\Routing\Redirector
     */
    protected function proccessPost()
    {
        if (Input::has('collection_action')) {
            $items = explode(',', Input::get('items', array()));

            if (empty($items))
                return Redirect::to(URL::current());
            
            switch (Input::get('collection_action')) {
                case 'delete':
                        $totalFailed = 0;

                        foreach ($items as $termId) {
                            $term = $this->getKotakin()->getTerm()->find($termId);

                            if ($term) {
                                if ( ! $this->deleting($term, true)) {
                                    ++$totalFailed;
                                }
                            }

                            unset($term);
                        }

                        if ($totalFailed > 0) {
                            Session::flash('error', $this->getUtility()->t('Some selected item(s) failed to delete or already deleted.'));
                        } else {
                            Session::flash('success', $this->getUtility()->t('Successfully deleting selected item(s).'));
                        }

                        return Redirect::to(URL::current());
                    break;
                
                case 'email':
                    $recipients = explode(',', Input::get('recipients', array()));
                    $subject = Input::get('subject', '');
                    $message = Input::get('message', '');

                    return $this->sendingMail($recipients, $subject, $message, $items);
                    break;
            }
        }

        if (Input::has('_new_folder')) {
            $folder = new Folder($this->getKotakin(), $this->getSentry());
            return $folder->create($this->folder, $this->term);
        }

        if (Input::has('_action')) {
            $itemId = (int) Input::get('item', 0);

            if (empty($itemId))
                App::abort(404);

            $term = $this->getKotakin()->getTerm()->newQuery()
                        ->where('id', '=', $itemId)->first();

            if ( ! $term)
                App::abort(404);

            switch (Input::get('_action')) {
                case 'rename':
                        return $this->renaming($term, Input::get('name', ''));
                    break;
                
                case 'delete':
                        return $this->deleting($term);
                    break;

                case 'move':
                        return $this->moving($term, Input::get('destination', ''));
                    break;

                case 'share':
                        return $this->sharing(
                            $term, 
                            Input::get('users', array()), 
                            (bool) Input::get('upload', 0)
                        );
                    break;

                case 'link':
                        return $this->linking($term, Input::all());
                    break;
            }
        }

        App::abort(404);
    }

    /**
     * Sending mail.
     *
     * @param array $recipients
     * @param string $subject
     * @param string $message
     * @param array $items
     * @return Illuminate\Routing\Redirector
     */
    protected function sendingMail($recipients, $subject, $message, $items)
    {
        $mediaList = array();
        $files = array();
        $fileList = array();

        foreach ($items as $index => $termId) {
            $term = $this->getKotakin()->getTerm()->newQuery()
                        ->where('id', '=', $termId)
                        ->first();

            if ($term) {
                if ($term->getAttribute('is_file')) {
                    $doc = $this->getKotakin()->getDoc()->newQuery()
                                ->where('id', '=', $term->getAttribute('object_id'))
                                ->first();

                    if ($doc) {
                        if ($doc->media) {
                            $fileList[] = ($index + 1).'. '.$doc->media->getAttribute('title');
                            $files[] = array(
                                'path' => storage_path().'/'.$doc->media->getAttribute('path').'/'.$doc->media->getAttribute('filename').'.'.$doc->media->getAttribute('extension'),
                                'name' => $doc->media->getAttribute('title')
                            );
                        }
                    }
                } else {
                    $folder = $this->getKotakin()->getFolder()->newQuery()
                                ->where('id', '=', $term->getAttribute('object_id'))
                                ->first();

                    $folderCtrl = new Folder($this->getKotakin(), $this->getSentry());
                    if (false !== ($media = $folderCtrl->download($folder, $term, true))) {
                        $fileList[] = ($index + 1).'. '.$media->getAttribute('title');
                        $mediaList[] = $media;
                        $files[] = array(
                            'path' => storage_path().'/'.$media->getAttribute('path').'/'.$media->getAttribute('filename').'.'.$media->getAttribute('extension'),
                            'name' => $media->getAttribute('title')
                        );
                    }
                }
            }
        }

        $continueSendingMail = true;

        if (count($items) != count($files)) {
            $continueSendingMail = false;
            Session::flash('error', $this->getUtility()->t('Some selected item(s) cannot be found and unable to attach.'));
        }

        if (empty($files)) {
            $continueSendingMail = false;
            Session::flash('error', $this->getUtility()->t('selected item(s) cannot be found and unable to attach.'));
        }

        if ( ! $continueSendingMail) {
            return Redirect::to(URL::current());
        }

        $message .= "\n\nFiles:\n";
        $message .= implode("\n", $fileList)."\n\n";

        $htmlMessage = nl2br($message);
        $plainMessage = $message;

        $mailSend = $this->getKotakin()->getMailer()->send('', array(
            'subject' => $subject,
            'html' => $htmlMessage,
            'plain' => $plainMessage
        ), function($message) use ($recipients, $files) {
            foreach ($recipients as $email) {
                $message->to(trim($email));
            }

            foreach ($files as $file) {
                $message->attach($file['path'], array(
                    'as' => $file['name']
                ));
            }
        });

        if ( ! $mailSend) {
            Session::flash('error', $this->getUtility()->t('Failed to send the email, please check your Email preferences or any server settings related.'));
        } else {
            Session::flash('success', $this->getUtility()->t('Email successfully sent.'));
        }

        if ( ! empty($mediaList)) {
            foreach ($mediaList as $media) {
                @unlink(storage_path().'/'.$media->getAttribute('path').'/'.$media->getAttribute('filename').'.'.$media->getAttribute('extension'));
                $media->delete();
            }
        }

        return Redirect::to(URL::current());
    }

    /**
     * Create public link.
     *
     * @param Sule\Kotakin\Models\TermInterface $term
     * @param array $input
     * @return Illuminate\Routing\Redirector
     */
    protected function linking(TermModelInterface $term, Array $input)
    {
        $doc = $this->getKotakin()->getDoc()->find($term->getAttribute('object_id'));

        if ($doc) {
            $fileName    = $term->getAttribute('name');
            $password    = Input::get('password', '');
            $limit       = (int) Input::get('limit', 0);
            $validUntil  = Input::get('valid_until', '');
            $description = Input::get('description', '');
            $slug        = $this->getKotakin()->getUUID()->v5($this->getKotakin()->config('uuid'), $this->getUtility()->randString().time());
            $user        = $this->getSentry()->getUserProvider()->getEmptyUser();

            $link = $this->getKotakin()->getDocLink()->newInstance();
            $link->fill(array(
                'document_id' => $doc->getId(),
                'author_id'   => $this->getSentry()->getUser()->getId(),
                'slug'        => $slug,
                'password'    => ( ! empty($password)) ? $user->hash($password) : '',
                'limit'       => (empty($limit)) ? -1 : $limit,
                'valid_until' => $validUntil.' 23:59:59',
                'description' => $description
            ));

            if ($link->save()) {
                if ( ! empty($password)) {
                    Session::flash('success', sprintf($this->getUtility()->t('Public link for "%s" with password "%s" successfully created: %s'), $fileName, $password, URL::to('/i/'.$slug)));
                } else {
                    Session::flash('success', sprintf($this->getUtility()->t('Public link for "%s" successfully created: %s'), $fileName, URL::to('/i/'.$slug)));
                }
            } else {
                Session::flash('error', sprintf($this->getUtility()->t('Failed to create public link for "%s".'), $fileName));
            }
        } else {
            Session::flash('error', $this->getUtility()->t('The file could not be found.'));
        }
        
        return Redirect::to(URL::current());
    }

    /**
     * Sharings folder.
     *
     * @param Sule\Kotakin\Models\TermInterface $term
     * @param array $users
     * @param bool $allowUserUpload
     * @return Illuminate\Routing\Redirector
     */
    protected function sharing(TermModelInterface $term, Array $users, $allowUserUpload)
    {
        $folder = new Folder($this->getKotakin(), $this->getSentry());
        return $folder->share($term, $users, $allowUserUpload);
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
            $folder = new Folder($this->getKotakin(), $this->getSentry());
            return $folder->rename($term, $name);
        } else {
            $doc = new Document($this->getKotakin(), $this->getSentry());
            return $doc->rename($term, $name);
        }
    }

    /**
     * moving term.
     *
     * @param Sule\Kotakin\Models\TermInterface $term
     * @param string $destination
     * @return Illuminate\Routing\Redirector
     */
    protected function moving(TermModelInterface $term, $destination)
    {
        $destination = ltrim($destination, '/');
        $destination = rtrim($destination, '/');

        $parentTerm   = null;
        $parentFolder = null;

        if (empty($destination)) {
            $parentId = 0;
        } else {
            $folderDestination = $this->getKotakin()->getFolder()->newQuery()
                                        ->where('slug', '=', $destination)
                                        ->first();

            if ( ! $folderDestination) {
                App::abort(404);
            } else {
                $parentTerm = $this->getKotakin()->getTerm()->newQuery()
                                    ->where('object_id', '=', $folderDestination->getId())
                                    ->first();

                if ( ! $parentTerm) {
                    App::abort(404);
                } else {
                    $parentFolder = $this->getKotakin()->getFolder()
                                        ->find($parentTerm->getAttribute('object_id'));

                    $parentId = $parentTerm->getId();
                }
            }

        }

        if ( ! empty($destination))
            $destination .= '/';

        $success = true;

        if ($term->getAttribute('is_folder') == 1) {
            $folder = $this->getKotakin()->getFolder()
                            ->find($term->getAttribute('object_id'));

            if ( ! $folder)
                App::abort(404);

            $folder->fill(array(
                'slug' => $destination.Str::slug($folder->getAttribute('name'))
            ));

            try {
                $success = $folder->save();
            } catch (FolderExistsException $e) {
                Session::flash('error', sprintf($this->getUtility()->t('Unable to move "%s" to "%s", please make sure no same folder (with uppercase or lower case name) there.'), $term->getAttribute('name'), '/'.$destination));
                return Redirect::to(URL::current());
            }
        }

        if ($success) {
            $term->fill(array(
                'parent_id' => $parentId
            ));
            $success = $term->save();

            if ( ! is_null($parentTerm) and ! is_null($parentFolder)) {
                if (count($parentTerm->shares) > 0) {
                    $users = array();
                    
                    foreach ($parentTerm->shares as $item) {
                        $users[] = $item->getAttribute('user_id');
                    }

                    $folder = new Folder($this->getKotakin(), $this->getSentry());

                    if ($term->getAttribute('is_folder') == 1) {
                        $folder->shareFolder($term, $users, (bool) $parentFolder->getAttribute('user_upload'));
                    } else {
                        $folder->shareFile($term, $users);
                    }

                    unset($folder);
                    unset($users);
                }
            }
        }

        if ( ! $success) {
            Session::flash('error', sprintf($this->getUtility()->t('Unable to move "%s" to "%s".'), $term->getAttribute('name'), '/'.$destination));
        } else {
            Session::flash('success', sprintf($this->getUtility()->t('"%s" successfully moved to "%s".'), $term->getAttribute('name'), '/'.$destination));
        }

        return Redirect::to(URL::current());
    }

    /**
     * Deleting term.
     *
     * @param Sule\Kotakin\Models\TermInterface $term
     * @param bool $returnBoolean
     * @return Illuminate\Routing\Redirector
     */
    protected function deleting(TermModelInterface $term, $returnBoolean = false)
    {
        if ($term->getAttribute('is_folder') == 1) {
            $folder = new Folder($this->getKotakin(), $this->getSentry());
            return $folder->delete($term, $returnBoolean);
        } else {
            $doc = new Document($this->getKotakin(), $this->getSentry());

            $theDoc = $this->getKotakin()->getDoc()->newQuery()
                            ->where('id', '=', $term->getAttribute('object_id'))->first();

            $name = $term->getAttribute('name');

            if ($theDoc) {
                $doc->delete($theDoc->getAttribute('slug'));
            } else {
                $doc->delete();
                $term->delete();
            }

            Session::flash('success', sprintf($this->getUtility()->t('File "%s" successfully deleted.'), $name));
        }

        if ($returnBoolean)
            return true;
        else
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

        return View::make('kotakin::admin_dashboard', array(
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

}