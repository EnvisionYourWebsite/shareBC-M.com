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
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Config;

use Exception;
use PDOException;

class Install extends Controller
{

    /**
     * Show the installer.
     *
     * @return Illuminate\View\View
     */
    public function index()
    {
        try {
            $user = DB::table('users')->first();
            App::abort(404);
        } catch (PDOException $e) {}

        $isBackgroundPathWriteable = true;
        $backgroundPath            = public_path().'/background';
        if ( ! File::isDirectory($backgroundPath) 
            or ! File::isWritable($backgroundPath))
            $isBackgroundPathWriteable = false;

        $isPackagePathWriteable = true;
        $packagePath            = public_path().'/packages';
        if ( ! File::isDirectory($packagePath) 
            or ! File::isWritable($packagePath))
            $isPackagePathWriteable = false;

        $isPreviewPathWriteable = true;
        $previewPath            = public_path().'/preview';
        if ( ! File::isDirectory($previewPath) 
            or ! File::isWritable($previewPath))
            $isPreviewPathWriteable = false;

        $isCSSPathWriteable = true;
        $cssPath            = public_path().'/packages/sule/kotakin/css';
        if ( ! File::isDirectory($cssPath) 
            or ! File::isWritable($cssPath))
            $isCSSPathWriteable = false;

        $isJSPathWriteable = true;
        $jsPath            = public_path().'/packages/sule/kotakin/js';
        if ( ! File::isDirectory($jsPath) 
            or ! File::isWritable($jsPath))
            $isJSPathWriteable = false;

        $isStoragePathWriteable = true;
        $storagePath            = storage_path();
        $storageFSPath          = storage_path().'/fs';
        $storageJitPath         = storage_path().'/jit';
        $storageKotakinPath     = storage_path().'/kotakin';
        $storageLogsPath        = storage_path().'/logs';
        $storageSessionsPath    = storage_path().'/sessions';
        $storageViewsPath       = storage_path().'/views';
        if (( ! File::isDirectory($storagePath) or ! File::isWritable($storagePath)) 
            or ( ! File::isDirectory($storageFSPath) or ! File::isWritable($storageFSPath)) 
            or ( ! File::isDirectory($storageJitPath) or ! File::isWritable($storageJitPath)) 
            or ( ! File::isDirectory($storageKotakinPath) or ! File::isWritable($storageKotakinPath)) 
            or ( ! File::isDirectory($storageLogsPath) or ! File::isWritable($storageLogsPath)) 
            or ( ! File::isDirectory($storageSessionsPath) or ! File::isWritable($storageSessionsPath)) 
            or ( ! File::isDirectory($storageViewsPath) or ! File::isWritable($storageViewsPath)))
            $isStoragePathWriteable = false;

        $isLangPathWriteable = true;
        $langPath            = base_path().'/workbench/sule/kotakin/src/lang';
        if ( ! File::isWritable($langPath))
            $isLangPathWriteable = false;

        $isAppConfigWriteable = true;
        $appConfigPath        = app_path().'/config/app.php';
        if ( ! File::isWritable($appConfigPath))
            $isAppConfigWriteable = false;

        $isDatabaseConfigWriteable = true;
        $databaseConfigPath        = app_path().'/config/database.php';
        if ( ! File::isWritable($databaseConfigPath))
            $isDatabaseConfigWriteable = false;

        if ($_POST) {
            if ($isPackagePathWriteable and (! $isCSSPathWriteable or ! $isJSPathWriteable)) {
                Artisan::call('asset:publish', array('--bench' => 'sule/kotakin'));
            }

            $validator = Validator::make(Input::all(), array(
                'timezone' => 'required'
            ));

            if ( ! $validator->fails()) {
                if ($isAppConfigWriteable) {
                    $handle = fopen($appConfigPath, 'rb');
                    $appContent = fread($handle, filesize($appConfigPath));
                    fclose($handle);

                    $baseUrlStr = "".'$'."sslSet = '';\n";
                    $baseUrlStr .= "if(isset(".'$'."_SERVER['HTTPS']) && ".'$'."_SERVER['HTTPS'] == 'on') {\n";
                    $baseUrlStr .= "    ".'$'."sslSet = 's';\n";
                    $baseUrlStr .= "}\n\n";

                    $baseUrlStr .= "".'$'."baseUrl = '';\n";
                    $baseUrlStr .= "if (isset(".'$'."_SERVER['HTTP_HOST'])) {\n";
                    $baseUrlStr .= "    ".'$'."baseUrl = 'http'.".'$'."sslSet.'://'.".'$'."_SERVER['HTTP_HOST'];\n";
                    $baseUrlStr .= "}\n\n";
                    $baseUrlStr .= "return array(";

                    if (strpos($appContent, '$sslSet') === false) {
                        $appContent = preg_replace("/return array\(/i", $baseUrlStr, $appContent);
                    }

                    $appContent = preg_replace("/'url'\s=>\s'(.*)',/i", "'url' => ".'$'."baseUrl,", $appContent);

                    $appContent = preg_replace("/'timezone'\s=>\s'(.*)',/i", "'timezone' => '".Input::get('timezone')."',", $appContent);

                    $key = str_replace("'", '', html_entity_decode($this->randString()));
                    $key = str_replace('\\', '', $key);
                    $key = substr($key, 0, 32);
                    $appContent = preg_replace("/'key'\s=>\s'(.*?)',/i", "'key' => '".$key."',", $appContent);

                    $providers = Config::get('app.providers');

                    if ( ! in_array('Cartalyst\Sentry\SentryServiceProvider', $providers)) {
                        $providers[] = 'Cartalyst\Sentry\SentryServiceProvider';
                    }

                    if ( ! in_array('TwigBridge\TwigServiceProvider', $providers)) {
                        $providers[] = 'TwigBridge\TwigServiceProvider';
                    }

                    if ( ! in_array('Thapp\JitImage\JitImageServiceProvider', $providers)) {
                        $providers[] = 'Thapp\JitImage\JitImageServiceProvider';
                    }

                    if ( ! in_array('Illuminate\Workbench\WorkbenchServiceProvider', $providers)) {
                        $providers[] = 'Illuminate\Workbench\WorkbenchServiceProvider';
                    }

                    $providers = implode("',\n        '", $providers);
                    $providers = "array(\n        '".$providers."'\n    )";

                    $appContent = preg_replace("/'providers'\s=>\sarray\((.*?)\),/is", "'providers' => ".$providers.",", $appContent);

                    $fp = fopen($appConfigPath, 'w');
                    fwrite($fp, $appContent);
                    fclose($fp);
                    
                    return Redirect::to('/install/db');
                }
            }

            return Redirect::to('/install')
                        ->withInput()->withErrors($validator->errors());
        }

        return view::make('kotakin::install/index', array(
            'menu'                      => 'path',
            'isBackgroundPathWriteable' => $isBackgroundPathWriteable,
            'backgroundPath'            => $backgroundPath,
            'isPackagePathWriteable'    => $isPackagePathWriteable,
            'packagePath'               => $packagePath,
            'isPreviewPathWriteable'    => $isPreviewPathWriteable,
            'previewPath'               => $previewPath,
            'isCSSPathWriteable'        => $isCSSPathWriteable,
            'cssPath'                   => $cssPath,
            'isJSPathWriteable'         => $isJSPathWriteable,
            'jsPath'                    => $jsPath,
            'isStoragePathWriteable'    => $isStoragePathWriteable,
            'storagePath'               => $storagePath,
            'isLangPathWriteable'       => $isLangPathWriteable,
            'langPath'                  => $langPath,
            'isAppConfigWriteable'      => $isAppConfigWriteable,
            'appConfigPath'             => $appConfigPath,
            'isDatabaseConfigWriteable' => $isDatabaseConfigWriteable,
            'databaseConfigPath'        => $databaseConfigPath,
            'timezone'                  => Config::get('app.timezone')
        ));
    }

    /**
     * Show the installer.
     *
     * @return Illuminate\View\View
     */
    public function db()
    {
        try {
            $user = DB::table('users')->first();
            return Redirect::to('/install/user');
        } catch (PDOException $e) {}

        $dbConfig = Config::get('database.connections.mysql');

        if ($_POST) {
            $validator = Validator::make(Input::all(), array(
                'db_name'   => 'required',
                'user_name' => 'required',
                'password'  => 'required',
                'db_host'   => 'required'
            ));

            $isError      = true;
            $errorMessage = '';

            if ( ! $validator->fails()) {
                $isError = false;

                Config::set('database.default', 'mysql');
                Config::set('database.connections.mysql.host', Input::get('db_host'));
                Config::set('database.connections.mysql.database', Input::get('db_name'));
                Config::set('database.connections.mysql.username', Input::get('user_name'));
                Config::set('database.connections.mysql.password', Input::get('password'));

                try {
                    $connection = DB::connection();
                } catch (PDOException $e) {
                    $isError      = true;
                    $errorMessage = $e->getMessage();
                }

                if ( ! $isError) {
                    Artisan::call('migrate', array('--bench' => 'sule/kotakin'));
                    Artisan::call('db:seed', array('--class' => 'Sule\Kotakin\Seeds\OptionTableSeeder'));
                    Artisan::call('db:seed', array('--class' => 'Sule\Kotakin\Seeds\GroupTableSeeder'));
                    Artisan::call('db:seed', array('--class' => 'Sule\Kotakin\Seeds\EmailTemplateTableSeeder'));

                    $dbConfig = Config::get('database.connections.mysql');

                    $databaseConfigPath = app_path().'/config/database.php';

                    $handle = fopen($databaseConfigPath, 'rb');
                    $dbContent = fread($handle, filesize($databaseConfigPath));
                    fclose($handle);

                    $dbContent = preg_replace("/'default'\s=>\s'(.*)',/i", "'default' => 'mysql',", $dbContent);

                    $dbStr = '';
                    foreach ($dbConfig as $key => $value) {
                        $dbStr .= "            '".$key."' => '".$value."',\n";
                    }

                    $dbStr = "array(\n".$dbStr."\n        )";

                    $dbContent = preg_replace("/'mysql'\s=>\sarray\((.*?)\),/is", "'mysql' => ".$dbStr.",", $dbContent);

                    $fp = fopen($databaseConfigPath, 'w');
                    fwrite($fp, $dbContent);
                    fclose($fp);

                    return Redirect::to('/install/user');
                }
            }

            if ($isError) {
                Session::flash('error', 'Please make sure your database connection details are correct: '.$errorMessage);
            }

            return Redirect::to('/install/db')
                        ->withInput()->withErrors($validator->errors());
        }

        return view::make('kotakin::install/database', array(
            'menu'    => 'db',
            'dbConfig' => $dbConfig
        ));
    }

    /**
     * Generate a random string.
     *
     * @param
     * len: int number char to return
     * num: boolean, to include numeric
     * uc: boolean, to include uppercase char
     * lc: boolean, to include lowercase char
     * oc: boolean, to include others char => !"#$%&'()*+,-./:;<=>?@[\]^_`{|}~
     * @return string
     */
    protected function randString($len = 50, $num = true, $uc = true, $lc = true, $oc = true)
    {
        if (!$len || $len < 1 || $len > 100)
            $len = 100;

        $s = '';
        $i = 0;
        do {
            switch(mt_rand(1,4)) {
                // get number - ASCII characters (0:48 through 9:57)
                case 1:
                    if ($num) {
                        $s .= chr(mt_rand(48,57));
                        $i++;
                    }
                break;
                // get uppercase letter - ASCII characters (a:65 through z:90)
                case 2:
                    if ($uc) {
                        $s .= chr(mt_rand(65,90));
                        $i++;
                    }
                break;
                // get lowercase letter - ASCII characters (A:97 through Z:122)
                case 3:
                    if ($lc) {
                        $s .= chr(mt_rand(97,122));
                        $i++;
                    }
                    break;
                // get other characters - ASCII characters
                // !"#$%&'()*+,-./ :;<=>?@ [\]^_` {|}~
                // (33-47, 58-64, 91-96, 123-126)
                case 4:
                    if ($oc) {
                        switch(mt_rand(1,4)) {
                            case 1:
                                $s .= "&#" . mt_rand(33,47) . ";";
                                $i++;
                                break;
                            case 2:
                                $s .= "&#" . mt_rand(58,64) . ";";
                                $i++;
                                break;
                            case 3:
                                $s .= "&#" . mt_rand(91,96) . ";";
                                $i++;
                                break;
                            case 4:
                                $s .= "&#" . mt_rand(123,126) . ";";
                                $i++;
                                break;
                        }
                    }
                break;
            }
        } while ($i < $len);

        return $s;
    }

}