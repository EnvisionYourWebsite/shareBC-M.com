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

use Sule\Kotakin\Templates\EmailInterface;

use Sule\Kotakin\Kotakin as CoreKotakin;
use Sule\Kotakin\Models\EmailTemplateInterface;

class Email implements EmailInterface
{
    /**
     * The Kotakin.
     *
     * @var Sule\Kotakin\Kotakin
     */
    protected $kotakin;

    /**
     * The email.
     *
     * @var array
     */
    protected $email;

    /**
     * Set the kotakin.
     *
     * @param Sule\Kotakin\Kotakin $kotakin
     * @return void
     */
    public function setKotakin(CoreKotakin $kotakin)
    {
        $this->kotakin = $kotakin;
    }

    /**
     * Get the kotakin.
     *
     * @return Sule\Kotakin\Kotakin
     */
    protected function getKotakin()
    {
        return $this->kotakin;
    }

    /**
     * Set the email data.
     *
     * @param Sule\Kotakin\Models\EmailTemplateInterface $email
     * @return void
     */
    public function setEmail(EmailTemplateInterface $email)
    {
        $this->email = $email;
    }

    /**
     * Return email template id.
     *
     * @return int
     */
    public function id()
    {
        if (!is_null($this->email))
            return $this->email->getAttribute('id');

        return 0;
    }

    /**
     * Return email template identifier.
     *
     * @return string
     */
    public function identifier()
    {
        if (!is_null($this->email))
            return $this->email->getAttribute('identifier');

        return '';
    }

    /**
     * Return email template formated identifier.
     *
     * @return string
     */
    public function title()
    {
        if (!is_null($this->email))
            return ucwords(implode(' ', explode('_', $this->identifier())));

        return '';
    }
    
    /**
     * Return email template subject.
     *
     * @param string $formValue
     * @return string
     */
    public function subject($formValue = '')
    {
        if (!empty($formValue))
            return $formValue;

        if (!is_null($this->email))
            return $this->email->getAttribute('subject');

        return '';
    }

    /**
     * Return email template html content.
     *
     * @param string $formValue
     * @return string
     */
    public function html($formValue = '')
    {
        if (!empty($formValue))
            return $formValue;

        if (!is_null($this->email))
            return $this->email->getAttribute('content_html');

        return '';
    }

    /**
     * Return email template plain content.
     *
     * @param string $formValue
     * @return string
     */
    public function plain($formValue = '')
    {
        if (!empty($formValue))
            return $formValue;

        if (!is_null($this->email))
            return $this->email->getAttribute('content_plain');

        return '';
    }

    /**
     * Return email template note.
     *
     * @return string
     */
    public function note()
    {
        if (!is_null($this->email))
            return $this->email->getAttribute('note');

        return '';
    }

    /**
     * Create a new instance of the given template.
     *
     * @return Sule\Kotakin\Templates\FileInterface|static
     */
    public function newInstance()
    {
        // This method just provides a convenient way for us to generate fresh template
        // instances of this current template. It is particularly useful during the
        // hydration of new objects.
        return new static;
    }

}
