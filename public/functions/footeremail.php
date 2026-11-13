<?php

require_once('phpmailer/class.phpmailer.php');

$mail = new PHPMailer();

if( isset( $_POST['quick-contact-form-submit'] ) AND $_POST['quick-contact-form-submit'] == 'submit' ) {
    
    if( $_POST['quick-contact-form-name'] != '' AND $_POST['quick-contact-form-email'] != '' AND $_POST['quick-contact-form-message'] != '' ) {
        
		$name = $_POST['quick-contact-form-name'];
        
        $email = $_POST['quick-contact-form-email'];
        
        $message = $_POST['quick-contact-form-message'];
        
        $toemail = 'noreply@semicolonweb.com'; // Your Email Address
        
        $toname = 'SemiColon'; // Your Name
        
		$body = "Name: $name \n\nEmail: $email \n\nMessage: $message";
        
        $mail->SetFrom( $email , $name );

        $mail->AddReplyTo( $email , $name );
        
        $mail->AddAddress( $toemail , $toname );
        
        $mail->Subject = 'Message from Footer Contact Widget';
        
        $mail->MsgHTML( $body );
        
        $sendEmail = $mail->Send();
        
        if( $sendEmail == true ):
        
            echo '<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert">&times;</button>Message <strong>successfully</strong> received.</div>';
        
        else:
            
            echo '<div class="alert alert-error"><button type="button" class="close" data-dismiss="alert">&times;</button><strong>Error.!</strong> Please Try Again later.</div>';
        
        endif;
        
    } else {
        
        echo '<div class="alert alert-error"><button type="button" class="close" data-dismiss="alert">&times;</button><strong>Fill up</strong> all the Fields.</div>';
    
    }
    
} else {

    echo '<div class="alert alert-error"><button type="button" class="close" data-dismiss="alert">&times;</button>An <strong>unexpected error</strong> occured. Please Try Again later.</div>';

}

?>