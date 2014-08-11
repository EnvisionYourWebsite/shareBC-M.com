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

class Editor extends Base
{

    /**
     * The template base dir.
     *
     * @var string
     */
    protected $viewDir;

    /**
     * The css base dir.
     *
     * @var string
     */
    protected $cssDir;

    /**
     * The js base dir.
     *
     * @var string
     */
    protected $jsDir;

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

        $this->viewDir = __DIR__.'/../../../../views/';
        $this->cssDir  = public_path().'/packages/sule/kotakin/css/';
        $this->jsDir   = public_path().'/packages/sule/kotakin/js/';
    }

    /**
     * Show editor page
     *
     * @return Illuminate\View\View
     */
	public function index()
	{
        $files = array(
            'template' => array(),
            'css'      => array(),
            'js'       => array()
        );

        $files['template'] = scandir($this->viewDir);
        $files['css']      = scandir($this->cssDir);
        $files['js']       = scandir($this->jsDir);

        foreach ($files['template'] as $index => $file) {
            if (substr($file, 0, 1) == '.' or is_dir($this->viewDir.$file)) {
                unset($files['template'][$index]);
            }
        }

        $files['template'] = array_values($files['template']);

        foreach ($files['css'] as $index => $file) {
            if (substr($file, 0, 1) == '.' or is_dir($this->viewDir.$file)) {
                unset($files['css'][$index]);
            }
        }

        $files['css'] = array_values($files['css']);

        foreach ($files['js'] as $index => $file) {
            if (substr($file, 0, 1) == '.' or is_dir($this->viewDir.$file)) {
                unset($files['js'][$index]);
            }
        }

        $files['js'] = array_values($files['js']);

        $file = Input::get('file', '');
        $type = Input::get('type', '');

        if (empty($file)) {
            $file = $files['template'][0];
            $type = 'template';
        }

        $filePath = '';

        switch ($type) {
            case 'template':
                $mode     = 'html';
                $filePath = $this->viewDir.$file;
                break;
            
            case 'css':
                $mode     = 'css';
                $filePath = $this->cssDir.$file;
                break;

            case 'js':
                $mode     = 'javascript';
                $filePath = $this->jsDir.$file;
                break;
        }

        $fileContent = '';
        if ( ! empty($filePath)) {
            $fileSize = filesize($filePath);

            if ( ! empty($fileSize)) {
                $handle = fopen($filePath, 'rb');
                $fileContent = fread($handle, $fileSize);
                fclose($handle);
            }
        }

        $this->getPage()->setActiveMenu('preference');
        $this->getPage()->setAttribute('title', $this->getUtility()->t('UI Editor | Admin'));

        $js = '<script type="text/javascript">';
        $js .= '$(function () {';
        $js .= '$("#editor").height($("#editor-sidebar").height() - 200);';
        $js .= 'var editor = ace.edit("editor");';
        $js .= 'editor.setTheme("ace/theme/monokai");';
        $js .= 'editor.getSession().setMode("ace/mode/'.$mode.'");';
        $js .= '$(".file-save-btn").click(function(){';
        $js .= '$("#file-content").val(editor.getSession().getValue());';
        $js .= '$("#file-save-frm").submit()';
        $js .= '});';
        $js .= '});';
        $js .= '</script>';

        $this->getPage()->setMetadata($js, 'footer');

        return View::make('kotakin::admin_editor', array(
			'page'        => $this->getPage(),
            'files'       => $files,
            'file'        => $file,
            'type'        => $type,
            'fileContent' => htmlentities($fileContent)
		));
	}

    /**
     * Save file
     *
     * @return Illuminate\Support\Facades\Redirect
     */
    public function save()
    {
        $file     = Input::get('file', '');
        $type     = Input::get('type', '');
        $content  = Input::get('content', '');
        $filePath = '';

        switch ($type) {
            case 'template':
                $filePath = $this->viewDir.$file;
                break;
            
            case 'css':
                $filePath = $this->cssDir.$file;
                break;

            case 'js':
                $filePath = $this->jsDir.$file;
                break;
        }

        if ( ! is_writable($filePath)) {
            Session::flash('error', sprintf($this->getUtility()->t('File "%s" is not writeable.'), $file));
        } else {
            if ( ! empty($file) and ! empty($filePath)) {
                $handle = fopen($filePath, 'w');
                fwrite($handle, $content);
                fclose($handle);
            }

            Session::flash('success', sprintf($this->getUtility()->t('File "%s" successfully saved.'), $file));
        }

        return Redirect::to(URL::current().'?file='.$file.'&type='.$type);
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