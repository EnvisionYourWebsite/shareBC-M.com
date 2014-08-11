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

class InvalidDataException extends \UnexpectedValueException {}
class OptionNotFoundException extends \OutOfBoundsException {}
class OptionExistsException extends \UnexpectedValueException {}
class EmailTemplateNotFoundException extends \OutOfBoundsException {}
class EmailTemplateExistsException extends \UnexpectedValueException {}
class MediaNotFoundException extends \OutOfBoundsException {}
class MediaExistsException extends \UnexpectedValueException {}
class TermNotFoundException extends \OutOfBoundsException {}
class TermExistsException extends \UnexpectedValueException {}
class TermSharingNotFoundException extends \OutOfBoundsException {}
class TermSharingExistsException extends \UnexpectedValueException {}
class DocumentNotFoundException extends \OutOfBoundsException {}
class DocumentExistsException extends \UnexpectedValueException {}
class DocumentLinkExistsException extends \UnexpectedValueException {}
class DocumentLinkNotFoundException extends \OutOfBoundsException {}
class UserProfileNotFoundException extends \OutOfBoundsException {}
class UserProfileExistsException extends \UnexpectedValueException {}
class EmailRecipientNotFoundException extends \OutOfBoundsException {}
class EmailRecipientExistsException extends \UnexpectedValueException {}
class FolderNotFoundException extends \OutOfBoundsException {}
class FolderExistsException extends \UnexpectedValueException {}
