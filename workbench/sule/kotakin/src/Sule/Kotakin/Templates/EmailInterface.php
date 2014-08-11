<?php
namespace Sule\Kotakin\Templates;

/*
 * This file is part of the Kotakin
 *
 * Author: Sulaeman <me@sulaeman.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

interface EmailInterface
{
    
    /**
     * Return email template id.
     *
     * @return int
     */
    public function id();

    /**
     * Return email template identifier.
     *
     * @return string
     */
    public function identifier();

    /**
     * Return email template formated identifier.
     *
     * @return string
     */
    public function title();
    
    /**
     * Return email template subject.
     *
     * @param string $formValue
     * @return string
     */
    public function subject($formValue = '');

    /**
     * Return email template html content.
     *
     * @param string $formValue
     * @return string
     */
    public function html($formValue = '');

    /**
     * Return email template plain content.
     *
     * @param string $formValue
     * @return string
     */
    public function plain($formValue = '');

    /**
     * Return email template note.
     *
     * @return string
     */
    public function note();

    /**
     * Create a new instance of the given template.
     *
     * @return Sule\Kotakin\Templates\FileInterface|static
     */
    public function newInstance();

}
