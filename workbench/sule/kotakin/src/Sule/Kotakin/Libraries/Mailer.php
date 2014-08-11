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
use Sule\Kotakin\Models\EmailTemplateInterface;

use Illuminate\Mail\Message;

use Closure;
use Swift_Mailer;
use Swift_Message;

use Sule\Kotakin\Models\EmailTemplateNotFoundException;

class Mailer implements MailerInterface
{

    /**
     * The swift mailer.
     *
     * @var Swift_Mailer
     */
    protected $swift;

    /**
     * The template model.
     *
     * @var Sule\Kotakin\Models\EmailTemplateInterface
     */
    protected $template;

    /**
     * The view parser.
     *
     * @var Twig_Environment
     */
    protected $view;

    /**
     * The current error message.
     *
     * @var string
     */
    protected $error;

    /**
     * Create a new instance.
     *
     * @param Sule\Kotakin\Models\EmailTemplateInterface $template
     * @param  Swift_Mailer  $swift
     * @return void
     */
    public function __construct(EmailTemplateInterface $template, Swift_Mailer $swift)
    {
        $this->template = $template;
        $this->swift    = $swift;
    }

    /**
     * Set the global from address and name.
     *
     * @param  string  $address
     * @param  string  $name
     * @return void
     */
    public function alwaysFrom($address, $name = null)
    {
        $this->from = compact('address', 'name');
    }

    /**
     * Set the global reply to address.
     *
     * @param  string  $address
     * @return void
     */
    public function alwaysReplyTo($address)
    {
        $this->replyTo = $address;
    }

    /**
     * Send a new message using a view.
     *
     * @param  string  $template
     * @param  array  $data
     * @param  Closure|string  $callback
     * @return void
     */
    public function send($template, array $data, $callback)
    {
        if ( ! empty($template)) {
            try {
                list($subject, $html, $plain) = $this->buildTemplate($template, $data);
            } catch (EmailTemplateNotFoundException $e) {
                return false;
            }
        } else {
            extract($data);
        }

        $message = $this->createMessage($subject);

        $this->callMessageBuilder($callback, $message);

        // Once we have retrieved the view content for the e-mail we will set the body
        // of this message using the HTML type.
        $this->addContent($message, $html, $plain);

        $message = $message->getSwiftMessage();

        return $this->sendSwiftMessage($message);
    }

    /**
     * Build the email content from template.
     *
     * @param  string  $template
     * @param  array  $data
     * @return array
     * @throws Sule\Kotakin\Models\EmailTemplateNotFoundException
     */
    public function buildTemplate($template, array $data)
    {
        $tpl = $this->getTemplate()->select('subject', 'content_html', 'content_plain')
                    ->where('identifier', '=', $template)
                    ->first();

        if ( ! is_object($tpl))
            throw new EmailTemplateNotFoundException("Template [$template] does not exist");

        $subject = $this->getView()->render($tpl->subject, $data);
        $html = $this->getView()->render($tpl->content_html, $data);
        $plain = $this->getView()->render($tpl->content_plain, $data);

        return array($subject, $html, $plain);
    }

    /**
     * Add the content to a given message.
     *
     * @param  \Illuminate\Mail\Message  $message
     * @param  string  $html
     * @param  string  $plain
     * @return void
     */
    protected function addContent($message, $html, $plain)
    {
        if (isset($html))
            $message->setBody($html, 'text/html');

        if (isset($plain))
            $message->addPart($plain, 'text/plain');
    }

    /**
     * Send a Swift Message instance.
     *
     * @param  Swift_Message  $message
     * @return void
     */
    protected function sendSwiftMessage($message)
    {
        return $this->swift->send($message);
    }

    /**
     * Call the provided message builder.
     *
     * @param  Closure|string  $callback
     * @param  \Illuminate\Mail\Message  $message
     * @return void
     */
    protected function callMessageBuilder($callback, $message)
    {
        if ($callback instanceof Closure) {
            return call_user_func($callback, $message);
        } elseif (is_string($callback)) {
            return $this->container[$callback]->mail($message);
        }

        throw new \InvalidArgumentException("Callback is not valid.");
    }

    /**
     * Create a new message instance.
     *
     * @param string $subject
     * @return \Illuminate\Mail\Message
     */
    protected function createMessage($subject)
    {
        $message = new Message(new Swift_Message);

        // If a global from address has been specified we will set it on every message
        // instances so the developer does not have to repeat themselves every time
        // they create a new message. We will just go ahead and push the address.
        if (isset($this->from['address']))
            $message->from($this->from['address'], $this->from['name']);

        // If a global replyTo address has been specified we will set it on every message
        // instances so the developer does not have to repeat themselves every time
        // they create a new message. We will just go ahead and push the address.
        if (isset($this->replyTo))
            $message->replyTo($this->replyTo);

        $message->subject($subject);

        return $message;
    }

    /**
     * Get the Swift Mailer instance.
     *
     * @return Swift_Mailer
     */
    public function getSwiftMailer()
    {
        return $this->swift;
    }

    /**
     * Set the Swift Mailer instance.
     *
     * @param  Swift_Mailer  $swift
     * @return void
     */
    public function setSwiftMailer($swift)
    {
        $this->swift = $swift;
    }

    /**
     * Get the Twig instance.
     *
     * @return Twig_Environment
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * Set the Twig instance.
     *
     * @param  Twig_Environment  $view
     * @return void
     */
    public function setView($view)
    {
        $this->view = $view;
    }

    /**
     * Get the template model instance.
     *
     * @return Sule\Kotakin\Models\EmailTemplateInterface
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Set the template model instance.
     *
     * @param  Sule\Kotakin\Models\EmailTemplateInterface  $template
     * @return void
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }

}
