<?php

require_once('contactus.class.php');

class ContactUsModel
{
    public $form;
	public $response;
	public $CMSContent; // reference to the master content holder (the CMS helper) - should this be a static // singleton instead?
	
 
    public function __construct(&$CMSContent) {
		$this->CMSContent = &$CMSContent;
		
		$this->response['result'] = true;	
		// todo get this from a template file
		$this->response['output'] = "<form id='contact-us' action='?action=handleSubmit' method='POST' >
				<div class='row'>
					<div class='large-4 medium-4 columns'>
						<label>Name:</label>
						<input id='name' name='name' placeholder='Enter your name' type='text'/> 
					</div>
					<div class='large-4 medium-4 columns'>
						<label>Email:</label>
						<input id='email' name='email' placeholder='Enter your email address' type='text'/> 
					</div>
					<div class='large-4 medium-4 columns'>
						<label>Phone:</label>
						<input id='phone' name='phone' placeholder='Enter your phone number' type='text'/> 
					</div>
				</div>
				<div class='row'>
					<div class='large-12 columns'>
						<label>Message</label>
						<textarea id='message' name='message' placeholder='Enter your message here'></textarea>
						<input id='leavemeblank' name='leavemeblank' placeholder='Leave this blan.k' type='text' class='visuallyhidden'/> 
					</div>
				</div>
				<div class='row'>
					<div class='small-6 large-6 small-centered columns'>
						<button type='submit'>Submit</button>
					</div>
				</div>
			</form>";
		$this->form = $this->response['output']; // copy this to the form too	
    }
	
	public function handleSubmit() {
		
		// validate the post
		if((isset($_POST['name']) && strlen($_POST['name']) > 0) &&
		//	(isset($_POST['email']) && strlen($_POST['email']) > 0) &&
			(isset($_POST['message']) && strlen($_POST['message']) > 0)
		) {		
	
			// get and sanitise input
			$name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
			$email = filter_var($_POST['email'], FILTER_SANITIZE_STRING);
			$phone = filter_var($_POST['phone'], FILTER_SANITIZE_STRING);
			$message = filter_var($_POST['message'], FILTER_SANITIZE_STRING);
						
			// validate the email
		//	error_log("HERE!!!!" . filter_var($email, FILTER_VALIDATE_EMAIL));
			if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
				$this->response['result'] = false;
				$this->response['output'] = $this->form . "<p class=\"error\">Your email address appears to be invalid, please check and try again!</p>";
				return false;
			}
			
			// check the HONEEEEYpot.
			if (strlen($_POST['leavemeblank']) > 0) {
				$this->response['result'] = false;
				$this->response['output'] = "Sorry you've supplied too much information!";
				return false;
			}
			
			error_log("Attempting an email send");
			// try and send the email
			$contactUs = new ContactUs();
			
			$this->response['result'] = $contactUs->sendEmail($name, $email, $phone, $message);
			// response
			$this->response['output'] = "Thank you - we'll be in touch real soon";
		} else {
			$this->response['result'] = false;
			$this->response['output'] = $this->form . "<p class=\"error\">Please check all required fields have been supplied.</p>";
		}
	}
 
}