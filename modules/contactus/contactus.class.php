<?php

class ContactUs {
	// A business logic class to keep the model skinny(ier)

	function sendEmail($name, $email, $phone, $message) {
	
		try{
			// subject
			$subject = 'Website Contact Us Message';
					
			$headers  = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-Type: text/plain;charset=utf-8' . "\r\n";
			
			// Additional headers
		//	$headers .= 'From: Website <'. EMAIL .'>' . "\r\n";
			// $headers .= 'Cc: stevemorgan@uk2.net' . "\r\n";
			
			$headers = 'From: '. EMAIL .'' . "\r\n" .
		   'Reply-To: '. EMAIL .'' . "\r\n" .
		   'X-Mailer: PHP/' . phpversion();

			$emailMessage = <<<HEREDOC
	The Following message has been submitted by:
	[[NAME]], [[EMAIL]], [[PHONE]]

	Message sent: 
	[[DATETIME]]
	
	Message:
	[[MESSAGE]]
HEREDOC;
// don't indent heredoc  or your get an error			
		$emailMessage = str_replace('[[NAME]]', $name, $emailMessage);
		$emailMessage = str_replace('[[EMAIL]]', $email, $emailMessage);
		$emailMessage = str_replace('[[PHONE]]', $phone, $emailMessage);
		$emailMessage = str_replace('[[DATETIME]]', date('l jS F Y h:i:s A'), $emailMessage);
		$emailMessage = str_replace('[[MESSAGE]]', $message, $emailMessage);

		// Mail it
		// error_log('testing error log');
		mail(EMAIL, $subject, $emailMessage, $headers);
		return true;
			
		} catch (exception $e) {
			error_log("Error sending email - $email - $subject - $message");
			return false;
		}
		
		return false;
	}	
}
?>
