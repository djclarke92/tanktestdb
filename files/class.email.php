<?php
//--------------------------------------------------------------------------------------
//
//	TankTest Website
//	Copyright (c) 2026 Dave Clarke
//
//--------------------------------------------------------------------------------------
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;



function func_email_examination_pdf( $ex_array, $exam_cylinder, $customers, $user )
{
	$rc = false;
	
	$dest = sprintf( "%s,%s", $customers[0]['cu_Email'], $user['us_Username'] );
	$subject = sprintf( "Cylinder Examination Document from %s", $user['us_StationName'] );
			
	$message = "";
	$message .= sprintf( "Hi %s,<br>", $customers[0]['cu_Firstname'] );
	$message .= sprintf( "<p>Atttached to this email is your Cylinder Document of Certification for the <b>%s</b> cylinder with Serial No. <b>%s</b>.</p>", 
		$exam_cylinder[0]['cy_Manufacturer'], $exam_cylinder[0]['cy_SerialNo'] );

	$message .= "<p>" . $_SESSION['regulations'] . "</p>";

	$message .= sprintf( "<p>Have a great day !<br>%s</p>", $user['us_Name'] );
	
	

	//Create an instance; passing `true` enables exceptions
	$mail = new PHPMailer(true);

	try {
		//Server settings
		//$mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
		$mail->isSMTP();                                            //Send using SMTP
		$mail->Host       = 'MP.MAIL.ISX.NET.NZ';                     //Set the SMTP server to send through
		$mail->SMTPAuth   = true;                                   //Enable SMTP authentication
		$mail->Username   = SMTP_USERNAME;                     //SMTP username
		$mail->Password   = SMTP_PASSWORD;                               //SMTP password
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
		$mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

		//Recipients
		$mail->setFrom( $user['us_Username'], $user['us_Name']);
		$mail->addAddress( $customers[0]['cu_Email'], sprintf("%s %s", $customers[0]['cu_Firstname'], $customers[0]['cu_Surname']) );     //Add a recipient
		//$mail->addAddress('ellen@example.com');               //Name is optional
		$mail->addReplyTo( $user['us_Username'], $user['us_Name']);
		$mail->addCC( $user['us_Username'] );
		//$mail->addBCC('bcc@example.com');

		//Attachments
		$pdf = sprintf( "report-%06d.pdf", $ex_array['ex_ExaminationNo'] );
		$mail->addAttachment($pdf);         //Add attachments

		//Content
		$mail->isHTML(true);                                  //Set email format to HTML
		$mail->Subject = $subject;
		$mail->Body    = $message;
		$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

		$mail->send();
		//echo 'Message has been sent';
		$rc = true;
	} catch (Exception $e) {
		echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
	}
	
	return $rc;
}






?>