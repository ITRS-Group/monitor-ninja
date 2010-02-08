<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Email helper class.
 *
 * $Id: email.php 3769 2008-12-15 00:48:56Z zombor $
 *
 * @package    Core
 * @author     Kohana Team
 * copyright  (c) 2007-2008 Kohana Team
 * license    http://kohanaphp.com/license.html
 */
class email_Core {

	// SwiftMailer instance
	protected static $mail;

	/**
	 * Creates a SwiftMailer instance.
	 *
	 * @param $config DSN connection string
	 * @return Swift object
	 */
	public static function connect($config = NULL)
	{
		if ( ! class_exists('Swift', FALSE))
		{
			// Load SwiftMailer
			require_once Kohana::find_file('vendor', 'swiftmailer/swift_required');
		}

		// Load default configuration
		($config === NULL) and $config = Kohana::config('email');

		switch ($config['driver'])
		{
			case 'smtp':
				// Set port
				$port = empty($config['options']['port']) ? NULL : (int) $config['options']['port'];

				// Create a SMTP connection
				$connection = Swift_SmtpTransport::newInstance( $config['options']['hostname'], $port );

				if (!empty($config['options']['encryption']))
				{
					// Set encryption
					switch (strtolower($config['options']['encryption']))
					{
						case 'tls':
						case 'ssl':
							$connection->setEncryption( $config['options']['encryption'] );
							break;
					}
				}

				// Do authentication, if part of the DSN
				empty($config['options']['username']) or $connection->setUsername($config['options']['username']);
				empty($config['options']['password']) or $connection->setPassword($config['options']['password']);

				if ( ! empty($config['options']['auth']))
				{
					// Get the class name and params
					list ($class, $params) = arr::callback_string($config['options']['auth']);

					if ($class === 'PopB4Smtp')
					{
						// Load the PopB4Smtp class manually, due to its odd filename
						require Kohana::find_file('vendor', 'swift/Swift/Authenticator/$PopB4Smtp$');
					}

					// Prepare the class name for auto-loading
					$class = 'Swift_Authenticator_'.$class;

					// Attach the authenticator
					$connection->attachAuthenticator(($params === NULL) ? new $class : new $class($params[0]));
				}

				// Set the timeout to 5 seconds
				$connection->setTimeout(empty($config['options']['timeout']) ? 5 : (int) $config['options']['timeout']);
			break;
			case 'sendmail':
				// Create a sendmail connection
				$connection = Swift_SendmailTransport::newInstance( $config['options'] );
			break;
			default:
				// Use the native connection
				$connection = Swift_MailTransport::newInstance();
			break;
		}

		// Create the SwiftMailer instance
		return email::$mail = Swift_Mailer::newInstance($connection);
	}

	/**
	 * Send an email message.
	 *
	 * @param $to recipient email (and name), or an array of To, Cc, Bcc names
	 * @param $from sender email (and name)
	 * @param $subject message subject
	 * @param $body message body
	 * @param $html send email as HTML
	 * @return number of emails sent
	 */
	public static function send($to, $from, $subject, $body, $html = FALSE)
	{
		// Connect to SwiftMailer
		(email::$mail === NULL) and email::connect();

		// Determine the message type
		$html = ($html === TRUE) ? 'text/html' : 'text/plain';

		// Create the message
		$message = Swift_Message::newInstance($subject);
		$message->setBody( $body, $html );

		if (is_string($to))
		{
			// Single recipient
			$recipients = $message->setTo($to);
		}
		elseif (is_array($to))
		{
			if (isset($to[0]) AND isset($to[1]))
			{
				// Create To: address set
				$to = array('to' => $to);
			}

			foreach ($to as $method => $set)
			{
				if ( ! in_array($method, array('to', 'cc', 'bcc')))
				{
					// Use To: by default
					$method = 'to';
				}

				// Create method name
				$method = 'add'.ucfirst($method);

				if (is_array($set))
				{
					// Add a recipient with name
					$message->$method($set[0], $set[1]);
				}
				else
				{
					// Add a recipient without name
					$message->$method($set);
				}
			}
		}

		if (is_string($from))
		{
			// From without a name
			$from = $message->setFrom($from);
		}
		elseif (is_array($from))
		{
			// From with a name
			$from = $message->setFrom( array($from[0] => $from[1]) );
		}

		return email::$mail->send($message);
	}

	/**
	 * Send an email message.
	 *
	 * @param $to  recipient email (and name), or an array of To, Cc, Bcc names
	 * @param $from  sender email (and name)
	 * @param $subject message subject
	 * @param $plain message body
	 * @param $html send email as HTML
	 * @param $attachments Attachments(?)
	 * @return number of emails sent
	 */
	public static function send_multipart($to, $from, $subject, $plain='', $html='', $attachments=array())
	{
		// Connect to SwiftMailer
		(email::$mail === NULL) and email::connect();

		// Create the message
		$message = Swift_Message::newInstance($subject);
		
		//Add some "parts"
		switch(true)
		{
			case (strlen($html) AND strlen($plain)):
				$message->setBody($html, 'text/html');
				$message->addPart($plain, 'text/plain');
				break;
				
			case (strlen($html)):
				$message->setBody($html, 'text/html');
				break;
				
			case (strlen($plain)):
				$message->setBody($plain, 'text/plain');
				break;
				
			default:
				$message->setBody('', 'text/plain');
		}

		if(!empty($attachments))
		{
			foreach( $attachments AS $file => $mime )
			{
				$filename = basename( $file );
				
				//Use the Swift_File class
				$message->attach(Swift_Attachment::fromPath( $file )->setFilename( $filename ));
			}
		}

		if (is_string($to))
		{
			// Single recipient
			$recipients = $message->setTo($to);
		}
		elseif (is_array($to))
		{
			if (isset($to[0]) AND isset($to[1]))
			{
				// Create To: address set
				$to = array('to' => $to);
			}

			foreach ($to as $method => $set)
			{
				if ( ! in_array($method, array('to', 'cc', 'bcc')))
				{
					// Use To: by default
					$method = 'to';
				}

				// Create method name
				$method = 'add'.ucfirst($method);

				if (is_array($set))
				{
					// Add a recipient with name
					$message->$method($set[0], $set[1]);
				}
				else
				{
					// Add a recipient without name
					$message->$method($set);
				}
			}
		}

		if (is_string($from))
		{
			// From without a name
			$from = $message->setFrom($from);
		}
		elseif (is_array($from))
		{
			// From with a name
			$from = $message->setFrom( array($from[0] => $from[1]) );
		}

		return email::$mail->send($message);
	}
} // End email
