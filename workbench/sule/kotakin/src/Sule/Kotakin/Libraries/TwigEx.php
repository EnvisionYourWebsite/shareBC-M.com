<?php
namespace Sule\Kotakin\Libraries;

/*
 * This file is part of the Kotakin
 *
 * Author: Sulaeman <me@sulaeman.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Illuminate\Foundation\Application;
use TwigBridge\Extension;

use Twig_Environment;
use Twig_Function_Method;
use Twig_Filter_Method;

use Sule\Kotakin\Libraries\Utility;

class TwigEx extends Extension
{
    /**
     * The utility.
     *
     * @var Sule\Kotakin\Libraries\Utility
     */
    protected $util;

    /**
     * Create a new instance.
     *
     * @param Sule\Kotakin\Kotakin
     * @return void
     */
    public function __construct(Application $app, Twig_Environment $twig)
    {
        parent::__construct($app, $twig);
    }

    /**
     * Sets the utility.
     *
     * @param  Sule\Kotakin\Libraries\Utility $util
     * @return void
     */
    public function setUtility(Utility $util)
    {
        $this->util = $util;
    }

    /**
     * Gets the utility.
     *
     * @return Sule\Kotakin\Libraries\Utility
     */
    public function getUtility()
    {
        return $this->util;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string Extension name.
     */
    public function getName()
    {
        return 'Ex';
    }

    /**
     * Define all the functions going to use
     *
     * @access    public
     * @return void
     */
    public function getFunctions()
    {
        return array(
            '_' => new Twig_Function_Method($this, 'translateFunction')
        );
    }

    /**
     * Define all the filters going to use
     *
     * @access    public
     * @return void
     */
    public function getFilters()
    {
        return array(
            'stripNewlines' => new Twig_Filter_Method($this, 'stripNewlinesFilter'),
            'handleize' => new Twig_Filter_Method($this, 'handleizeFilter'),
            'replace' => new Twig_Filter_Method($this, 'strReplaceFilter'),
            'charLimiter' => new Twig_Filter_Method($this, 'charLimiterFilter')
        );
    }

    public function translateFunction($str, $domain = '')
    {
        return $this->util->t($str);
    }

    /**
     * Strip all new line in string
     *
     * @access    public
     * @return string
     */
    public function stripNewlinesFilter($str)
    {
        $str = str_replace("\r", '', $str);
        $str = str_replace("\n", '', $str);

        return $str;
    }

    /**
     * Sanitize the string
     *
     * @access    public
     * @return string
     */
    public function handleizeFilter($str)
    {
        return $this->getUtility()->sanitizeText($str, true);
    }

    /**
     * str_replace php function interface
     *
     * @access    public
     * @return string
     */
    public function strReplaceFilter($subject, $search, $replace)
    {
        return str_replace($search, $replace, $subject);
    }

    /**
     * Character Limiter
     *
     * Limits the string based on the character count.  Preserves complete words
     * so the character count may not be exactly as specified.
     *
     * @access  public
     * @param   string
     * @param   integer
     * @param   string  the end character. Usually an ellipsis
     * @return  string
     */
    public function charLimiterFilter($str, $n = 500, $end_char = '&#8230;')
    {
        return $this->getUtility()->characterLimiter($str, $n, $end_char);
    }
}
