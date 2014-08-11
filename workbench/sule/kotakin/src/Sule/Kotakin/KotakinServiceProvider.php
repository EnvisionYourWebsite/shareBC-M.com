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

use Illuminate\Support\ServiceProvider;
use Thapp\JitImage\JitImageServiceProvider;

use Illuminate\Support\Facades\DB;

use Sule\Kotakin\Kotakin;

use Sule\Kotakin\Models\UserProvider;
use Sule\Kotakin\Models\Option;
use Sule\Kotakin\Models\EmailTemplate as EmailModelTemplate;
use Sule\Kotakin\Models\Media;
use Sule\Kotakin\Models\Term;
use Sule\Kotakin\Models\Folder;
use Sule\Kotakin\Models\TermSharing;
use Sule\Kotakin\Models\Document;
use Sule\Kotakin\Models\DocumentLink;
use Sule\Kotakin\Models\EmailRecipient;

use Sule\Kotakin\Libraries\UUID;
use Sule\Kotakin\Libraries\Utility;

use Sule\Kotakin\Templates\User as UserTemplate;
use Sule\Kotakin\Templates\Page as PageTemplate;
use Sule\Kotakin\Templates\Folder as FolderTemplate;
use Sule\Kotakin\Templates\Document as DocumentTemplate;
use Sule\Kotakin\Templates\DocumentLink as DocumentLinkTemplate;
use Sule\Kotakin\Templates\File as FileTemplate;
use Sule\Kotakin\Templates\Term as TermTemplate;
use Sule\Kotakin\Templates\Email as EmailTemplate;

use Sule\Kotakin\Libraries\TwigString;
use Sule\Kotakin\Libraries\Mailer;
use Swift_Mailer;
use Swift_SmtpTransport as SmtpTransport;
use Swift_MailTransport as MailTransport;
use Swift_SendmailTransport as SendmailTransport;

use Sule\Kotakin\Commands\RemoveExpiredLink;
use Sule\Kotakin\Commands\RemoveDlLimitedLink;

use Exception;

class KotakinServiceProvider extends ServiceProvider
{

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('sule/kotakin');

		// Load the artisan
        // include __DIR__.'/../../artisan.php';

		// Load the filters
        include __DIR__.'/../../filters.php';

        // Load the routes
        include __DIR__.'/../../routes.php';
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		// Override the sentry user provider
		$this->app['sentry.user'] = $this->app->share(function($app) {
			$model = 'Sule\Kotakin\Models\User';
			
			// We will never be accessing a user in Sentry without accessing
			// the user provider first. So, we can lazily setup our user
			// model's login attribute here. If you are manually using the
			// attribute outside of Sentry, you will need to ensure you are
			// overriding at runtime.
			if (method_exists($model, 'setLoginAttribute')) {
				$loginAttribute = $app['config']['cartalyst/sentry::users.login_attribute'];

				forward_static_call_array(
					array($model, 'setLoginAttribute'),
					array($loginAttribute)
				);
			}

			return new UserProvider($app['sentry.hasher'], $model);
		});

		$this->app['kotakin.option'] = $this->app->share(function($app) {
			return new Option();
		});

		$this->app['kotakin.uuid'] = $this->app->share(function($app) {
			return new UUID();
		});

		$this->app['kotakin.emailTemplate'] = $this->app->share(function($app) {
			return new EmailModelTemplate();
		});

		$this->app['kotakin.util'] = $this->app->share(function($app) {
			return new Utility();
		});

		$this->app['kotakin.tpl'] = $this->app->share(function($app) {
			$page = new PageTemplate();
			
			$page->setUser(new UserTemplate());
			$page->setTerm(new TermTemplate());
			$page->setFolder(new FolderTemplate());
			$page->setDoc(new DocumentTemplate());
			$page->setDocLink(new DocumentLinkTemplate());
			$page->setFile(new FileTemplate());
			$page->setEmail(new EmailTemplate());

			return $page;
		});

		$this->app['kotakin.term'] = $this->app->share(function($app) {
			return new Term();
		});

		$this->app['kotakin.folder'] = $this->app->share(function($app) {
			return new Folder();
		});

		$this->app['kotakin.termSharing'] = $this->app->share(function($app) {
			return new TermSharing();
		});

		$this->app['kotakin.media'] = $this->app->share(function($app) {
			return new Media();
		});

		$this->app['kotakin.doc'] = $this->app->share(function($app) {
			return new Document();
		});

		$this->app['kotakin.docLink'] = $this->app->share(function($app) {
			return new DocumentLink();
		});

		$this->app['kotakin.emailRecipient'] = $this->app->share(function($app) {
			return new EmailRecipient();
		});

		$self = $this;

		$this->app['kotakin'] = $this->app->share(function($app) use ($self) {
			$kotakin = new Kotakin();

			try {
				$options = $app['kotakin.option']->first();
			} catch (Exception $e) {
				return $kotakin;
			}

			$kotakin->setOption($app['kotakin.option']);

			$kotakin->getOptions();

			$self->registerMailer(array(
				'driver'     => $kotakin->config('mail_driver'), 
				'host'       => $kotakin->config('mail_host'), 
				'port'       => $kotakin->config('mail_port'), 
				'from'       => unserialize($kotakin->config('mail_from')), 
				'replyTo'    => $kotakin->config('mail_reply_to'), 
				'encryption' => $kotakin->config('mail_encryption'), 
				'username'   => $kotakin->config('mail_username'), 
				'password'   => $kotakin->config('mail_password'), 
				'sendmail'   => $kotakin->config('mail_sendmail')
			));

			$langDir = __DIR__.'/../../lang';
			
			$app['kotakin.util']->setLocale($kotakin->config('locale'), $langDir);

			$kotakin->setUUID($app['kotakin.uuid']);
			$kotakin->setEmailTemplate($app['kotakin.emailTemplate']);
			$kotakin->setMailer($app['kotakin.mailer']);
			$kotakin->setUtility($app['kotakin.util']);
			$kotakin->setTemplate($app['kotakin.tpl']);
			$kotakin->setMedia($app['kotakin.media']);
			$kotakin->setTerm($app['kotakin.term']);
			$kotakin->setFolder($app['kotakin.folder']);
			$kotakin->setTermSharing($app['kotakin.termSharing']);
			$kotakin->setDoc($app['kotakin.doc']);
			$kotakin->setDocLink($app['kotakin.docLink']);
			$kotakin->setEmailRecipient($app['kotakin.emailRecipient']);

			// Recall the JitImage service provider
			$imageDriver = $kotakin->config('image_driver');
			$app['config']->set('jitimage::driver', $imageDriver);
			$app['config']->set('jitimage::cache.path', storage_path());
			$app['config']->set('jitimage::cache.route', 'storage/jit');
			if ($imageDriver == 'im') {
				$app['config']->set('jitimage::imagemagick.path', $kotakin->config('imagemagick_path'));
			}

			$jit = new JitImageServiceProvider($app);
			$jit->register();
			unset($jit);

			return $kotakin;
		});

		unset($self);

		$this->registerCommands();
	}

	public function registerMailer($config)
	{
		$this->registerTwigString();
		$this->registerSwiftMailer($config);

		$this->app['kotakin.mailer'] = $this->app->share(function($app) use ($config) {
			// Once we have create the mailer instance, we will set a container instance
			// on the mailer. This allows us to resolve mailer classes via containers
			// for maximum testability on said classes instead of passing Closures.
			$mailer = new Mailer($app['kotakin.emailTemplate'], $app['kotakin.swift.mailer']);

			$mailer->setView($app['kotakin.view.str']);

			// If a "from" address is set, we will set it on the mailer so that all mail
			// messages sent by the applications will utilize the same "from" address
			// on each one, which makes the developer's life a lot more convenient.
			if (is_array($config['from']) and isset($config['from']['address']))
				$mailer->alwaysFrom($config['from']['address'], $config['from']['name']);

			// If a "replty_to" address is set, we will set it on the mailer so that all mail
			// messages sent by the applications will utilize the same "replty_to" address
			// on each one, which makes the developer's life a lot more convenient.
			if ( ! empty($config['replyTo']))
				$mailer->alwaysReplyTo($config['replyTo']);

			return $mailer;
		});
	}

	/**
     * Register the twig string view engine
     * We use the twigbride options
     *
     * @return void
     */
    protected function registerTwigString()
    {
        $this->app['kotakin.view.str'] = $this->app->share(function($app) {
            $bridge = new TwigString($app);
            return $bridge->getTwig();
        });
    }

	/**
	 * Register the Swift Mailer instance.
	 *
	 * @return void
	 */
	protected function registerSwiftMailer($config)
	{
		$this->registerSwiftTransport($config);

		// Once we have the transporter registered, we will register the actual Swift
		// mailer instance, passing in the transport instances, which allows us to
		// override this transporter instances during app start-up if necessary.
		$this->app['kotakin.swift.mailer'] = $this->app->share(function($app) {
			return new Swift_Mailer($app['kotakin.swift.transport']);
		});
	}

	/**
	 * Register the Swift Transport instance.
	 *
	 * @param  array  $config
	 * @return void
	 */
	protected function registerSwiftTransport($config)
	{
		switch ($config['driver'])
		{
			case 'smtp':
				return $this->registerSmtpTransport($config);

			case 'sendmail':
				return $this->registerSendmailTransport($config);

			case 'mail':
				return $this->registerMailTransport($config);

			default:
				throw new \InvalidArgumentException('Invalid mail driver.');
		}
	}

	/**
	 * Register the SMTP Swift Transport instance.
	 *
	 * @param  array  $config
	 * @return void
	 */
	protected function registerSmtpTransport($config)
	{
		$this->app['kotakin.swift.transport'] = $this->app->share(function($app) use ($config) {
			extract($config);

			// The Swift SMTP transport instance will allow us to use any SMTP backend
			// for delivering mail such as Sendgrid, Amazon SMS, or a custom server
			// a developer has available. We will just pass this configured host.
			$transport = SmtpTransport::newInstance($host, $port);

			if (isset($encryption))
				$transport->setEncryption($encryption);

			// Once we have the transport we will check for the presence of a username
			// and password. If we have it we will set the credentials on the Swift
			// transporter instance so that we'll properly authenticate delivery.
			if (isset($username)) {
				$transport->setUsername($username);

				$transport->setPassword($password);
			}

			return $transport;
		});
	}

	/**
	 * Register the Sendmail Swift Transport instance.
	 *
	 * @param  array  $config
	 * @return void
	 */
	protected function registerSendmailTransport($config)
	{
		$this->app['kotakin.swift.transport'] = $this->app->share(function($app) use ($config) {
			return SendmailTransport::newInstance($config['sendmail']);
		});
	}

	/**
	 * Register the Mail Swift Transport instance.
	 *
	 * @param  array  $config
	 * @return void
	 */
	protected function registerMailTransport($config)
	{
		$this->app['kotakin.swift.transport'] = $this->app->share(function() {
			return MailTransport::newInstance();
		});
	}

	/**
     * Register the artisan commands.
     *
     * @return void
     */
    protected function registerCommands()
    {
        // Remove any expired link command
        $this->app['command.kotakin.removeExpiredLink'] = $this->app->share(
            function ($app) {
                return new RemoveExpiredLink($app['kotakin']);
            }
        );

        // Remove any download limit reached link command
        $this->app['command.kotakin.removeDlLimitedLink'] = $this->app->share(
            function ($app) {
                return new RemoveDlLimitedLink($app['kotakin']);
            }
        );

        $this->commands(
        	'command.kotakin.removeExpiredLink', 
        	'command.kotakin.removeDlLimitedLink'
        );
    }

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('kotakin');
	}

}