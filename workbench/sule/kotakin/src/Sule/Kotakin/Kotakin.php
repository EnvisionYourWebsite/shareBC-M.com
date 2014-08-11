<?php
namespace Sule\Kotakin;

/*
 * This file is part of the Kotakin
 *
 * Author: Sulaeman <me@sulaeman.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Sule\Kotakin\Models\OptionInterface;
use Sule\Kotakin\Models\EmailTemplateInterface;
use Sule\Kotakin\Models\MediaInterface;
use Sule\Kotakin\Libraries\MailerInterface;
use Sule\Kotakin\Models\TermInterface;
use Sule\Kotakin\Models\FolderInterface;
use Sule\Kotakin\Models\TermSharingInterface;
use Sule\Kotakin\Models\DocumentInterface;
use Sule\Kotakin\Models\DocumentLinkInterface;
use Sule\Kotakin\Models\EmailRecipientInterface;

use Sule\Kotakin\Libraries\UUID;
use Sule\Kotakin\Libraries\Utility;
use Sule\Kotakin\Templates\PageInterface as PageTemplateInterface;

class Kotakin
{

    /**
     * The option.
     *
     * @var Sule\Kotakin\Models\OptionInterface
     */
    protected $option;

    /**
     * The UUID.
     *
     * @var Sule\Kotakin\Libraries\UUID
     */
    protected $uuid;

    /**
     * The email template.
     *
     * @var Sule\Kotakin\Models\EmailTemplateInterface
     */
    protected $emailTemplate;

    /**
     * The media model.
     *
     * @var Sule\Kotakin\Models\MediaInterface
     */
    protected $media;

    /**
     * The term.
     *
     * @var Sule\Kotakin\Models\TermInterface
     */
    protected $term;

    /**
     * The term sharing.
     *
     * @var Sule\Kotakin\Models\TermSharingInterface
     */
    protected $termSharing;

    /**
     * The folder.
     *
     * @var Sule\Kotakin\Models\FolderInterface
     */
    protected $folder;

    /**
     * The document.
     *
     * @var Sule\Kotakin\Models\DocumentInterface
     */
    protected $document;

    /**
     * The document link.
     *
     * @var Sule\Kotakin\Models\DocumentLinkInterface
     */
    protected $documentLink;

    /**
     * The mailer.
     *
     * @var Sule\Kotakin\Libraries\MailerInterface
     */
    protected $mailer;

    /**
     * The utility.
     *
     * @var Sule\Kotakin\Libraries\Utility
     */
    protected $utility;

    /**
     * The main template, used for retrieving
     * objects which implement the Kotakin template
     * interface.
     *
     * @var Sule\Kotakin\Templates\PageInterface
     */
    protected $template;

    /**
     * The options.
     *
     * @var array
     */
    protected $options;

    /**
     * The email recipient.
     *
     * @var Sule\Kotakin\Models\EmailRecipientInterface
     */
    protected $emailRecipient;

    /**
     * Gets option for Kotakin by given name.
     *
     * @param string $name
     * @return string | array
     */
    public function config($name)
    {
        $option = '';
        $options = $this->getOptions();

        if (isset($options[$name]))
            $option = $options[$name]->getAttribute('value');

        unset($options);

        return $option;
    }

    /**
     * Gets all existing options for Kotakin.
     *
     * @return array
     */
    public function getOptions()
    {
        if (is_null($this->options)) {
            $this->options = $this->getOption()->all();

            if (!empty($this->options)) {
                foreach ($this->options as $index => $item) {
                    $this->options[$item->name] = $item;
                    unset($this->options[$index]);
                }
            }
        }

        return $this->options;
    }

    /**
     * Sets the UUID for Kotakin.
     *
     * @param  Sule\Kotakin\Libraries\UUID $uuid
     * @return void
     */
    public function setUUID(UUID $uuid)
    {
        $this->uuid = $uuid;
    }

    /**
     * Gets the UUID for Kotakin.
     *
     * @return Sule\Kotakin\Libraries\UUID
     */
    public function getUUID()
    {
        return $this->uuid;
    }

    /**
     * Sets the option for Kotakin.
     *
     * @param  Sule\Kotakin\Models\OptionInterface $option
     * @return void
     */
    public function setOption(OptionInterface $option)
    {
        $this->option = $option;
    }

    /**
     * Gets the option for Kotakin.
     *
     * @return Sule\Kotakin\Models\OptionInterface
     */
    public function getOption()
    {
        return $this->option;
    }

    /**
     * Sets the email template for Kotakin.
     *
     * @param  Sule\Kotakin\Models\EmailTemplateInterface $emailTemplate
     * @return void
     */
    public function setEmailTemplate(EmailTemplateInterface $emailTemplate)
    {
        $this->emailTemplate = $emailTemplate;
    }

    /**
     * Gets the email template for Kotakin.
     *
     * @return Sule\Kotakin\Models\EmailTemplateInterface
     */
    public function getEmailTemplate()
    {
        return $this->emailTemplate;
    }
    
    /**
     * Sets the media model for Kotakin.
     *
     * @param  Sule\Kotakin\Models\MediaInterface $media
     * @return void
     */
    public function setMedia(MediaInterface $media)
    {
        $this->media = $media;
    }

    /**
     * Gets the media model for Kotakin.
     *
     * @return Sule\Kotakin\Models\MediaInterface
     */
    public function getMedia()
    {
        return $this->media;
    }

    /**
     * Sets the term for Kotakin.
     *
     * @param  Sule\Kotakin\Models\TermInterface $term
     * @return void
     */
    public function setTerm(TermInterface $term)
    {
        $this->term = $term;
    }

    /**
     * Gets the term for Kotakin.
     *
     * @return Sule\Kotakin\Models\TermInterface
     */
    public function getTerm()
    {
        return $this->term;
    }

    /**
     * Sets the term sharing for Kotakin.
     *
     * @param  Sule\Kotakin\Models\TermSharingInterface $term
     * @return void
     */
    public function setTermSharing(TermSharingInterface $term)
    {
        $this->termSharing = $term;
    }

    /**
     * Gets the term sharing for Kotakin.
     *
     * @return Sule\Kotakin\Models\TermSharingInterface
     */
    public function getTermSharing()
    {
        return $this->termSharing;
    }

    /**
     * Sets the folder for Kotakin.
     *
     * @param  Sule\Kotakin\Models\FolderInterface $folder
     * @return void
     */
    public function setFolder(FolderInterface $folder)
    {
        $this->folder = $folder;
    }

    /**
     * Gets the folder for Kotakin.
     *
     * @return Sule\Kotakin\Models\FolderInterface
     */
    public function getFolder()
    {
        return $this->folder;
    }

    /**
     * Sets the document for Kotakin.
     *
     * @param  Sule\Kotakin\Models\DocumentInterface $document
     * @return void
     */
    public function setDoc(DocumentInterface $document)
    {
        $this->document = $document;
    }

    /**
     * Gets the document for Kotakin.
     *
     * @return Sule\Kotakin\Models\DocumentInterface
     */
    public function getDoc()
    {
        return $this->document;
    }

    /**
     * Sets the document link for Kotakin.
     *
     * @param  Sule\Kotakin\Models\DocumentLinkInterface $documentLink
     * @return void
     */
    public function setDocLink(DocumentLinkInterface $documentLink)
    {
        $this->documentLink = $documentLink;
    }

    /**
     * Gets the document link for Kotakin.
     *
     * @return Sule\Kotakin\Models\DocumentLinkInterface
     */
    public function getDocLink()
    {
        return $this->documentLink;
    }

    /**
     * Sets the mailer for Kotakin.
     *
     * @param  Sule\Kotakin\Libraries\MailerInterface $mailer
     * @return void
     */
    public function setMailer(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * Gets the mailer for Kotakin.
     *
     * @return Sule\Kotakin\Libraries\MailerInterface
     */
    public function getMailer()
    {
        return $this->mailer;
    }

    /**
     * Sets the utility for Kotakin.
     *
     * @param  Sule\Kotakin\Libraries\Utility $utility
     * @return void
     */
    public function setUtility(Utility $utility)
    {
        $this->utility = $utility;
    }

    /**
     * Gets the utility for Kotakin.
     *
     * @return Sule\Kotakin\Libraries\Utility
     */
    public function getUtility()
    {
        return $this->utility;
    }

    /**
     * Sets the main template for Kotakin.
     *
     * @param  Sule\Kotakin\Templates\PageInterface $template
     * @return void
     */
    public function setTemplate(PageTemplateInterface $template)
    {
        $this->template = $template;
    }

    /**
     * Gets the main template for Kotakin.
     *
     * @return Sule\Kotakin\Templates\PageInterface
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Sets the main email recipient for Kotakin.
     *
     * @param  Sule\Kotakin\Templates\EmailRecipientInterface $emailRecipient
     * @return void
     */
    public function setEmailRecipient(EmailRecipientInterface $emailRecipient)
    {
        $this->emailRecipient = $emailRecipient;
    }

    /**
     * Gets the main email recipient for Kotakin.
     *
     * @return Sule\Kotakin\Templates\EmailRecipientInterface
     */
    public function getEmailRecipient()
    {
        return $this->emailRecipient;
    }

}
