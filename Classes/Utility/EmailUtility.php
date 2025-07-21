<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2020 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Utility;

use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;use TYPO3\CMS\Extbase\Validation\Validator\EmailAddressValidator;
use Erigo\ErigoBase\Exception\EmailAddressException;

class EmailUtility implements SingletonInterface
{
	public static function sendEmail(
	    array $options, 
	    string $templateName = null, 
	    array $rootPaths = [], 
	    array $variables = [], 
		array $attachments = [],
    ): void 
    {
		static::getEmail($options, $templateName, $rootPaths, $variables, $attachments)->send();
	}
	
	public static function getEmail(
	    array $options, 
	    string $templateName = null, 
	    array $rootPaths = [], 
	    array $variables = [], 
		array $attachments = [],
    ): MailMessage 
    {
		$emailValidator = GeneralUtility::makeInstance(EmailAddressValidator::class);
		$mail = GeneralUtility::makeInstance(MailMessage::class);
		
	// from
		if (array_key_exists('from', $options)) {
			$options['from'] = static::prepareEmailArray($emailValidator, $options['from']);
			
			if (count($options['from']) != 1) {
				throw new \InvalidArgumentException('The "from" option has to contain only one email address.');
			}
			
			$mail->setFrom($options['from']);
			
		} else {
			throw new \InvalidArgumentException('The "from" option is required.');
		}
		
	// subject
		if (array_key_exists('subject', $options)) {
			if (empty($options['subject'])) {
				throw new \InvalidArgumentException('The "subject" option can not be empty.');
			}
			
			$mail->setSubject($options['subject']);
			
		} else {
			throw new \InvalidArgumentException('The "subject" option is required.');
		}
		
	// to
		if (array_key_exists('to', $options)) {
			$options['to'] = static::prepareEmailArray($emailValidator, $options['to']);
			
			if (count($options['to']) < 1) {
				throw new \InvalidArgumentException('The "to" option has to contain at least one email address.');
			}
			
			$mail->setTo($options['to']);
			
		} else {
			throw new \InvalidArgumentException('The "to" option is required.');
		}
		
	// reply to
		if (array_key_exists('replyTo', $options)) {
			$options['replyTo'] = static::prepareEmailArray($emailValidator, $options['replyTo']);
			
			if (count($options['replyTo']) != 1) {
				throw new \InvalidArgumentException('The "replyTo" option can contain only one email address.');
			}

			if (count($options['replyTo']) > 0) {
				$mail->setReplyTo($options['replyTo']);
			}
		}
		
	// cc
		if (array_key_exists('cc', $options)) {
			$options['cc'] = static::prepareEmailArray($emailValidator, $options['cc']);

			if (count($options['cc']) > 0) {
				$mail->setCc($options['cc']);
			}
		}
		
	// bcc
		if (array_key_exists('bcc', $options)) {
			$options['bcc'] = static::prepareEmailArray($emailValidator, $options['bcc']);

			if (count($options['bcc']) > 0) {
				$mail->setBcc($options['bcc']);
			}
		}

	// content type
		if (!array_key_exists('contentType', $options)) {
			$options['contentType'] = 'text/html';
		}
		
	// body
		if (array_key_exists('body', $options)) {
			if (empty($options['body'])) {
				throw new \InvalidArgumentException('The "body" option can not be empty.');
			}
			
			if ($options['contentType'] == 'text/html') {
    			$mail->html(static::formatEmailBody($options['body']));
    			
			} else {
			    $mail->text($options['body']);
			}
			
		} else {
			if ($templateName != null) {
    			if ($options['contentType'] == 'text/html') {
        			$mail->html(static::formatEmailBody(static::getEmailBody($templateName, $rootPaths, $variables)));
        			
    			} else {
    				$mail->text(static::getEmailBody($templateName, $rootPaths, $variables));
    			}
				
			} else {
				throw new \InvalidArgumentException('The "body" option or template name is required.');
			}
		}
		
	// attachments
		foreach ($attachments as $attachment) {
			static::addAttachment($mail, $attachment);
		}
		
		return $mail;
	}
	
	protected static function getEmailBody(string $templateName, array $rootPaths = [], array $variables = []): string
	{
		$variables['constants'] = TypoScriptUtility::getFrontendConstants();
		
		return FluidUtility::getStandaloneView($templateName, $rootPaths, $variables)->render();
	}
	
	public static function formatEmailBody(string $emailBody): string
	{
		/**
		 * @todo
		 */
		
		return $emailBody;
	}
	
	protected static function prepareEmailArray(EmailAddressValidator $emailValidator, array|string $emails): array
	{
		$emailArray = [];
		
		if (!is_array($emails)) {
			$emails = [$emails => $emails];
		}
		
		foreach ($emails as $email => $name) {
			if (MathUtility::canBeInterpretedAsInteger($email)) {
				$email = $name;
			}
			
			if (empty($name)) {
				$name = $email;
			}
			
			$result = $emailValidator->validate($email);
			
			if ($result->hasErrors()) {
				$exceptionMessage = $result->getFirstError()->getMessage();
				
				if (is_string($email) && !empty($email)) {
					$exceptionMessage = '"'. $email .'": '. $exceptionMessage;
				}
				
				throw new EmailAddressException($exceptionMessage);
			}
			
			$emailArray[$email] = $name;
		}
		
		return $emailArray;
	}
	
	public static function getEmailRootPaths(array $rootPaths): array
	{
		$emailRootPaths = [];
		$pathTypes = ['template', 'partial', 'layout'];
		
		foreach ($pathTypes as $pathType) {
			$fullPathType = $pathType .'RootPaths';
			
			if (array_key_exists($fullPathType, $rootPaths)) {
				$emailRootPaths[$fullPathType] = [];
				
				foreach ($rootPaths[$fullPathType] as $priority => $rootPath) {
					$emailRootPaths[$fullPathType][$priority] = $rootPath .'Email/';
				}
			}
		}
		
		return $emailRootPaths;
	}
	
	protected static function addAttachment(MailMessage $mail, $attachment): void
	{
		if (is_array($attachment)) {
			$attachment = new \Swift_Attachment(
			    $attachment['data'], 
			    $attachment['filename'], 
			    $attachment['contentType'],
		    );
		}
		
		/**
		 * @todo Resource...
		 */
		
		if ($attachment instanceof \Swift_Attachment) {
			$mail->attach($attachment);
		}
	}
}