<?php

class EmailVerificationService {
	public function verifyAddress($email) {
		// Use VerifyEmail library if available
		if (class_exists('VerifyEmail\\VerifyEmail')) {
			$ve = new VerifyEmail\VerifyEmail($email, false);
			$ve->setConnectionTimeout(5);
			$ve->setStreamTimeout(5);
			try {
				return $ve->verify(); // boolean
			} catch (Exception $e) {
				return false;
			}
		}
		// Fallback: basic filter validation
		return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
	}

	public function sendVerificationEmail($toEmail, $toName, $token) {
		require_once __DIR__ . '/Mailer.php';
		$mailer = new Mailer();

		$verifyLink = $this->buildVerificationLink($token);
		$subject = 'Verify your email - Campus Events';
		$body = '<p>Hi ' . htmlspecialchars($toName) . ',</p>' .
				'<p>Please confirm your email address by clicking the link below:</p>' .
				'<p><a href="' . htmlspecialchars($verifyLink) . '">Verify my email</a></p>' .
				'<p>If you did not request this, you can ignore this email.</p>';

		return $mailer->sendEmail($toEmail, $toName, $subject, $body, []);
	}

	private function buildVerificationLink($token) {
		$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
		$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
		$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/');
		// Point to api/auth.php verify endpoint
		return $scheme . '://' . $host . $basePath . '/auth.php?action=verify_signup&token=' . urlencode($token);
	}
}

?>

