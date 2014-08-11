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

use Sule\Kotakin\Controllers\Base;

use Sule\Kotakin\Kotakin;
use Cartalyst\Sentry\Sentry;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;
use SplFileObject;

class i10n extends Base
{

    /**
     * Create a new instance.
     *
     * @param Sule\Kotakin\Kotakin $kotakin
     * @param Cartalyst\Sentry\Sentry $sentry
     * @param string $slug
     * @return void
     */
    public function __construct(Kotakin $kotakin, Sentry $sentry)
    {
        parent::__construct($kotakin, $sentry);
    }

    public function fire()
    {
        $baseDir = __DIR__.'/../../../';
        $dir = $baseDir.'lang/id_ID/LC_MESSAGES';
        
        if ( ! File::isDirectory($dir)) {
            File::makeDirectory($dir, Config::get('kotakin::chmod_folder'), true);
        }

        // $this->getTexts($baseDir, $dir.'/');
    }

    private function getTexts($path, $targetPath, $defaultDomain = 'kotakin')
    {
        if(is_dir($path)) {
            if ($handle = opendir($path)) {
                while (false !== ($item = readdir($handle))) {
                    if($item != '.' && $item != '..') {
                        $nextPath = $path.$item;

                        if (is_dir($nextPath)) {
                            echo $nextPath.'<br/>'."\n";
                            
                            $this->getTexts($nextPath.'/', $targetPath, $defaultDomain);
                        } else {
                            $textsNeedToRecord = array();
                            
                            echo $nextPath.'<br/>'."\n";
                            
                            $fp = @fopen($nextPath, 'r');
                            $contents = @fread($fp, filesize($nextPath));
                            @fclose($fp);
                            
                            preg_match_all("/Util::t\('([^']+)'(?(?=,)(?(?=,\s+),\s+|,)'([^']+)')\)/i", $contents, $matches);
                            
                            if (!empty($matches)) {
                                if (!empty($matches[1])) {
                                    foreach($matches[1] as $index => $item) {
                                        $textsNeedToRecord[] = array(
                                            'text'   => $item,
                                            'domain' => (!empty($matches[2][$index])) ? $matches[2][$index] : $defaultDomain
                                        );
                                    }
                                }
                            }

                            preg_match_all('/Util::t\("([^"]+)"(?(?=,),(?(?=,\s+),\s+|,)"([^"]+)")\)/i', $contents, $matches);
                            
                            if (!empty($matches)) {
                                if (!empty($matches[1])) {
                                    foreach($matches[1] as $index => $item) {
                                        $textsNeedToRecord[] = array(
                                            'text'   => $item,
                                            'domain' => (!empty($matches[2][$index])) ? $matches[2][$index] : $defaultDomain
                                        );
                                    }
                                }
                            }

                            preg_match_all("/_\('([^']+)'(?(?=,)(?(?=,\s+),\s+|,)'([^']+)')\)/i", $contents, $matches);
                            
                            if (!empty($matches)) {
                                if (!empty($matches[1])) {
                                    foreach($matches[1] as $index => $item) {
                                        $textsNeedToRecord[] = array(
                                            'text'   => $item,
                                            'domain' => (!empty($matches[2][$index])) ? $matches[2][$index] : $defaultDomain
                                        );
                                    }
                                }
                            }

                            preg_match_all('/_\("([^"]+)"(?(?=,),(?(?=,\s+),\s+|,)"([^"]+)")\)/i', $contents, $matches);
                            
                            if (!empty($matches)) {
                                if (!empty($matches[1])) {
                                    foreach($matches[1] as $index => $item) {
                                        $textsNeedToRecord[] = array(
                                            'text'   => $item,
                                            'domain' => (!empty($matches[2][$index])) ? $matches[2][$index] : $defaultDomain
                                        );
                                    }
                                }
                            }

                            if (!empty($textsNeedToRecord)) {
                                foreach($textsNeedToRecord as $item) {
                                    $isExist = false;
                                    $targetFile = $targetPath.$item['domain'].'.po';

                                    if (file_exists($targetFile)) {
                                        $lines = file($targetFile);
                                        if (!empty($lines)) {
                                            foreach ($lines as $line_num => $line) {
                                                if (strpos($line, $item['text']) > 0) {
                                                    $existingLineNum = ($line_num - 2);
                                                    $isExist = true;
                                                }
                                            }
                                        }
                                    }

                                    if (!$isExist) {
                                        if (strpos($item['text'], '"') !== false) {
                                            $item['text'] = str_replace('"', '\"', $item['text']);
                                        }

                                        $content = "msgid \"".$item['text']."\" \r\n";
                                        $content .= "msgstr \"\" \r\n\r\n";

                                        $fileWrite = new SplFileObject($targetFile, 'a');
                                        $fileWrite->fwrite($content);
                                    }
                                }
                            }
                        }
                    }
                }
            }

            closedir($handle);
        }
    }

}
