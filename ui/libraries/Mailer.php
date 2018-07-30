<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


require_once(APPPATH.'libraries/PHPMailer/Exception.php');
require_once(APPPATH.'libraries/PHPMailer/PHPMailer.php');
require_once(APPPATH.'libraries/PHPMailer/SMTP.php');

class Mailer {
	
	public function __construct(){
		$this->mail = new PHPMailer(true);
		$this->mail->IsSMTP();
		$this->mail->SMTPDebug  = 0;

		$this->mail->Host       = "smtp.mxhichina.com";
		$this->mail->Port       = 465;
		$this->mail->SMTPAuth   = true;
		$this->mail->CharSet	= "utf-8";
		$this->mail->SMTPSecure = "ssl";
		$this->mail->Username   = "system@zhiweihl.com";
		$this->mail->Password   = "#XiDuoLong123$";
		$this->mail->setFrom('system@zhiweihl.com','SSP平台');
	}

	public function sendMails($arrEmails,$name,$title,$body){
		try{
			$this->mail->isHTML(true);
            foreach($arrEmails as $email) {
			    $this->mail->AddAddress($email, $name);
            }
			$this->mail->Subject = $title;
			$this->mail->Body    = $body;

			$this->mail->Send();
			//echo "Message Sent OK</p>\n";

			return true;
		} catch (phpmailerException $e) {
			return false;
			//echo $e->errorMessage();
		} catch (Exception $e) {
			return false;
			//echo $e->getMessage();
		}
	}

	public function sendMail($email,$name,$title,$body){
		try{
			$this->mail->isHTML(true);
			$this->mail->AddAddress($email, $name);

			$this->mail->Subject = $title;
			$this->mail->Body    = $body;

			$this->mail->Send();
			//echo "Message Sent OK</p>\n";

			return true;
		} catch (phpmailerException $e) {
			return false;
			//echo $e->errorMessage();
		} catch (Exception $e) {
			return false;
			//echo $e->getMessage();
		}
	}
}

?>
