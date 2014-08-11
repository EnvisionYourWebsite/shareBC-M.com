<?php
namespace Sule\Kotakin\Models;

/*
 * This file is part of the Kotakin
 *
 * Author: Sulaeman <me@sulaeman.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Illuminate\Database\Eloquent\Model;

use Sule\Kotakin\Models\EmailTemplateInterface;

use Sule\Kotakin\Models\EmailTemplateExistsException;

class EmailTemplate extends Model implements EmailTemplateInterface
{

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'email_templates';

	/**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = array();

    /**
     * Returns the email's table name.
     *
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Returns the email's ID.
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->getKey();
    }

    /**
     * Saves the email.
     *
     * @param  array  $options
     * @return bool
     */
    public function save(array $options = array())
    {
        $this->validate();
        return parent::save();
    }

    /**
     * Delete the email.
     *
     * @return bool
     */
    public function delete()
    {
        return parent::delete();
    }

    /**
     * Validates the email and throws a number of
     * Exceptions if validation fails.
     *
     * @return bool
     * @throws Sule\Kotakin\Models\InvalidDataException
     * @throws Sule\Kotakin\Models\EmailTemplateExistsException
     */
    public function validate()
    {
        // Check if identifier field was passed
        if ( ! $identifier = $this->identifier)
            throw new InvalidDataException("A identifier is required for a email template, none given.");

        // Check if subject field was passed
        if ( ! $this->subject)
            throw new InvalidDataException("A subject is required for a email template, none given.");

        // Check if content_html field was passed
        if ( ! $this->content_html)
            throw new InvalidDataException("A content html is required for a email template, none given.");

        // Check if option already exists
        $query = $this->newQuery();
        $persistedEmail = $query->where('identifier', '=', $identifier)->first();

        if ($persistedEmail and $persistedEmail->getId() != $this->getId())
            throw new EmailTemplateExistsException("A email template already exists with identifier [$identifier], identifier must be unique for email template.");

        return true;
    }

}