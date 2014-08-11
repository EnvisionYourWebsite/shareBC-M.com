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

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;

use PDOException;

class Home extends Controller
{

	/**
     * Show the home page.
     * Testing if there was a DB problem, got to installer if exist.
     *
     * @return Illuminate\Routing\Redirector | Abort
     */
    public function index()
    {
        try {
            $user = DB::table('users')->first();
            App::abort(404);
        } catch (PDOException $e) {
            return Redirect::to('/install');
        }
    }

}