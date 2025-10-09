<?php

class Mailer {
	private $config;
	private $lastError = '';

	public function __construct() {
		$this->config = require __DIR__ . '/../config/mail.php';
	}

	public function sendEmail($toEmail, $toName, $subject, $htmlBody, $attachments = []) {
		if ($this->config['use_smtp']) {
			return $this->sendViaSmtp($toEmail, $toName, $subject, $htmlBody, $attachments);
		}
		return $this->sendViaMail($toEmail, $toName, $subject, $htmlBody, $attachments);
	}

	private function sendViaSmtp($toEmail, $toName, $subject, $htmlBody, $attachments) {
		// If PHPMailer is installed via composer, prefer using it
		$phpMailerAvailable = class_exists('PHPMailer\\PHPMailer\\PHPMailer');
		if ($phpMailerAvailable) {
			return $this->sendWithPHPMailer($toEmail, $toName, $subject, $htmlBody, $attachments);
		}
		// Fallback: without PHPMailer we cannot send attachments reliably
		if (!empty($attachments)) {
			$this->lastError = 'PHPMailer not installed; cannot send attachments.';
			return false;
		}
		return $this->sendViaMail($toEmail, $toName, $subject, $htmlBody, $attachments);
	}

	private function sendWithPHPMailer($toEmail, $toName, $subject, $htmlBody, $attachments) {
		$fromEmail = !empty($this->config['from_email']) ? $this->config['from_email'] : $this->config['username'];
		$fromName = !empty($this->config['from_name']) ? $this->config['from_name'] : 'Mailer';

		$mail = new PHPMailer\PHPMailer\PHPMailer(true);
		try {
			$mail->isSMTP();
			$mail->Host = $this->config['host'];
			$mail->Port = (int)$this->config['port'];
			$mail->SMTPAuth = (bool)$this->config['sMTPAuth'];
			if (!empty($this->config['sMTPSecure'])) {
				$mail->SMTPSecure = $this->config['sMTPSecure'];
			}
			$mail->Username = $this->config['username'];
			$mail->Password = $this->config['password'];

			$mail->CharSet = 'UTF-8';
			$mail->setFrom($fromEmail, $fromName);
			$mail->addAddress($toEmail, $toName ?: $toEmail);
			$mail->isHTML(true);
			$mail->Subject = $subject;
			$mail->Body = $htmlBody;

			foreach ($attachments as $path) {
				if (is_string($path) && file_exists($path)) {
					$mail->addAttachment($path);
				}
			}

			$ok = $mail->send();
			if (!$ok) {
				$this->lastError = $mail->ErrorInfo ?: 'Unknown error sending email';
			}
			return $ok;
		} catch (Exception $e) {
			$this->lastError = method_exists($mail, 'ErrorInfo') && $mail->ErrorInfo ? $mail->ErrorInfo : $e->getMessage();
			return false;
		}
	}

	private function sendViaMail($toEmail, $toName, $subject, $htmlBody, $attachments) {
		// Basic mail() fallback without attachments support
		$headers = [];
		$headers[] = 'MIME-Version: 1.0';
		$headers[] = 'Content-type: text/html; charset=UTF-8';
		$headers[] = 'From: ' . $this->config['from_name'] . ' <' . $this->config['from_email'] . '>';

		$ok = mail($toEmail, $subject, $htmlBody, implode("\r\n", $headers));
		if (!$ok) {
			$this->lastError = 'mail() returned false (check local mail configuration)';
		}
		return $ok;
	}

	public function getLastError() {
		return $this->lastError;
	}
}

?>

