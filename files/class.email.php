<?php
//--------------------------------------------------------------------------------------
//
//	TankTest Website
//	Copyright (c) 2026 Dave Clarke
//
//--------------------------------------------------------------------------------------




function func_get_email_styles()
{
	$style = "";
	$style .= "<style type=\"text/css\">";
	$style .= "* { font-family: Verdana, Arial, Helvetica, sans-serif;}";
	$style .= "style-normal { font-family: Verdana, Arial, Helvetica, sans-serif;}";
	$style .= "</style>";
	
	return $style;
}

function func_send_mail( $to, $body, $subject, $fromaddress, $fromname, $attachments = false )
{
	$eol="\n";
	$mime_boundary = md5(time());
	
	# Common Headers
	$headers = "";
	$headers .= "From: ".$fromname."<".$fromaddress.">".$eol;
	$headers .= "Reply-To: ".$fromname."<".$fromaddress.">".$eol;
	$headers .= "Return-Path: ".$fromname."<".$fromaddress.">".$eol;    // these two to set reply address
	$headers .= "Message-ID: <".time()."-".$fromaddress.">".$eol;
	$headers .= "X-Mailer: PHP v".phpversion().$eol;          // These two to help avoid spam-filters

	# Boundry for marking the split & Multitype Headers
	$headers .= "MIME-Version: 1.0".$eol;
	$headers .= "Content-Type: multipart/mixed; boundary=\"".$mime_boundary."\"".$eol.$eol;

	# Open the first part of the mail
	$msg = "--".$mime_boundary.$eol;
 
	$htmlalt_mime_boundary = $mime_boundary."_htmlalt"; //we must define a different MIME boundary for this section
	# Setup for text OR html -
	$msg .= "Content-Type: multipart/alternative; boundary=\"".$htmlalt_mime_boundary."\"".$eol.$eol;

	# Text Version
	$msg .= "--".$htmlalt_mime_boundary.$eol;
	$msg .= "Content-Type: text/plain; charset=us-ascii".$eol;
	$msg .= "Content-Transfer-Encoding: 8bit".$eol.$eol;
	$msg .= wordwrap(strip_tags(str_replace("<br>", "\n", substr($body, (strpos($body, "<body>")+6)))),70).$eol.$eol;

	# HTML Version
	$msg .= "--".$htmlalt_mime_boundary.$eol;
	$msg .= "Content-Type: text/html; charset=us-ascii".$eol;
//	$msg .= "Content-Transfer-Encoding: 8bit".$eol.$eol;
//	$msg .= $body.$eol.$eol;
	$msg .= "Content-Transfer-Encoding: base64".$eol.$eol;
	$msg .= wordwrap(base64_encode($body),70,"\n",true).$eol.$eol;

	//close the html/plain text alternate portion
	$msg .= "--".$htmlalt_mime_boundary."--".$eol.$eol;

	if ($attachments !== false)
	{
		for($i=0; $i < count($attachments); $i++)
		{
			if (is_file($attachments[$i]["file"]))
			{  
				# File for Attachment
				$file_name = substr($attachments[$i]["file"], (strrpos($attachments[$i]["file"], "/")+1));
       
				$handle=fopen($attachments[$i]["file"], 'rb');
				$f_contents=fread($handle, filesize($attachments[$i]["file"]));
				$f_contents=chunk_split(base64_encode($f_contents));    //Encode The Data For Transition using base64_encode();
				$f_type=filetype($attachments[$i]["file"]);
				fclose($handle);
       
				# Attachment
				$msg .= "--".$mime_boundary.$eol;
				$msg .= "Content-Type: ".$attachments[$i]["content_type"]."; name=\"".$file_name."\"".$eol;  // sometimes i have to send MS Word, use 'msword' instead of 'pdf'
				$msg .= "Content-Transfer-Encoding: base64".$eol;
				$msg .= "Content-Description: ".$file_name.$eol;
				$msg .= "Content-Disposition: attachment; filename=\"".$file_name."\"".$eol.$eol; // !! This line needs TWO end of lines !! IMPORTANT !!
				$msg .= $f_contents.$eol.$eol;
			}
		}
	}

	# Finished
	$msg .= "--".$mime_boundary."--".$eol.$eol;  // finish with two eol's for better security. see Injection.
 
	# SEND THE EMAIL
	ini_set( "sendmail_from", $fromaddress );  // the INI lines are to force the From Address to be used !
	$mail_sent = mail( $to, $subject, $msg, $headers );
 
	ini_restore( "sendmail_from" );
 
	return $mail_sent;
}

function func_email_examintaion_pdf( $ex_array, $customers, $user )
{
	$rc = false;
	
	$dest = sprintf( "%s,%s", $customers[0]['cu_Email'], $user['us_Email'] );
	$subject = sprintf( "Cylinder Examination Document from %s", $user['us_StationName'] );
			
	$message = "";
	$message .= "<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">";
	$message .= sprintf( "<html><title>%s</title>", $subject );
	$message .= sprintf( "<head>%s</head>", func_get_email_styles() );	// these styles do not always work ???
	$message .= sprintf( "<body %s %s>", func_show_bgcolor(), func_show_bgimage() );
	$message .= sprintf( "<table width=\"100%%\" height=\"100%%\" %s %s><tr valign=\"top\"><td>", func_show_bgcolor(), func_show_bgimage() );
	if ( $user['us_Logo'] != "" )
		$message .= sprintf( "%s<br><br>", func_get_website_logo() );
	$message .= sprintf( "<span style=\"font-family: Verdana; font-size: 10pt;\">" );
	
	$message .= sprintf( "Hi %s,<br><br>", $customers[0]['cu_Firstname'] );
	$message .= sprintf( "Atttached to this email is your Cylinder Document of Certification for the %s cylinder with Serial No. %s.<br><br>", $ex_array['ex_Manufacturer'], $ex_array['ex_SerialNo'] );

	$message .= sprintf( "Have a great day !<br>%s", $user['us_Firstname'] );
	
	$message .= sprintf( "</span></td></tr></table></body>" );
	$message .= sprintf( "</html>" );
	
	if ( func_send_mail( $dest, $message, $user['us_StationName'], $user['us_Email'], "admin@tanktestdb.nz" ) ===  true )
	{	// success;
		$rc = true;
	}
	
	return $rc;
}






?>