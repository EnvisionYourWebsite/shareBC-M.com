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

use Sule\Kotakin\Libraries\MailerInterface;

use Illuminate\Mail\Message;

use Closure;
use Swift_Mailer;
use Swift_Message;

interface MailerInterface
{

    /**
     * Set the global from address and name.
     *
     * @param  string  $address
     * @param  string  $name
     * @return void
     */
    public function alwaysFrom($address, $name = null);

    /**
     * Set the global reply to address.
     *
     * @param  string  $address
     * @return void
     */
    public function alwaysReplyTo($address);

    /**
     * Send a new message using a view.
     *
     * @param  string  $template
     * @param  array  $data
     * @param  Closure|string  $callback
     * @return void
     */
    public function send($template, array $data, $callback);

    /**
     * Build the email content from template.
     *
     * @param  string  $template
     * @param  array  $data
     * @return array
     * @throws Sule\Kotakin\Models\EmailTemplateNotFoundException
     */
    public function buildTemplate($template, array $data);

    /**
     * Get the Swift Mailer instance.
     *
     * @return Swift_Mailer
     */
    public function getSwiftMailer();

    /**
     * Set the Swift Mailer instance.
     *
     * @param  Swift_Mailer  $swift
     * @return void
     */
    public function setSwiftMailer($swift);

    /**
     * Get the Twig instance.
     *
     * @return Twig_Environment
     */
    public function getView();

    /**
     * Set the Twig instance.
     *
     * @param  Twig_Environment  $view
     * @return void
     */
    public function setView($view);

    /**
     * Get the template model instance.
     *
     * @return Sule\Kotakin\Models\EmailTemplateInterface
     */
    public function getTemplate();

    /**
     * Set the template model instance.
     *
     * @param  Sule\Kotakin\Models\EmailTemplateInterface  $template
     * @return void
     */
    public function setTemplate($template);

}
