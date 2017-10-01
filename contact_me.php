<?php
if($_POST)
{
    require "Mail.php";
    $to_email       = "contact@uncoupdepousse.fr";
    
    //check if its an ajax request, exit if not
    if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
        
        $output = json_encode(array( //create JSON data
            'type'=>'error', 
            'text' => 'Sorry Request must be Ajax POST'
        ));
        die($output); //exit script outputting json data
    } 
    
    //Sanitize input data using PHP filter_var().
    $user_name      = filter_var($_POST["name"], FILTER_SANITIZE_STRING);
    $user_email     = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
    $copyme         = filter_var($_POST["copyme"], FILTER_SANITIZE_STRING);
    $phone_number   = filter_var($_POST["phone"], FILTER_SANITIZE_NUMBER_INT);
    $subject        = filter_var($_POST["subject"], FILTER_SANITIZE_STRING);
    $message        = filter_var($_POST["msg"], FILTER_SANITIZE_STRING);
    
    //additional php validation
    if(strlen($user_name)<4){ // If length is less than 4 it will output JSON error.
        $output = json_encode(array('type'=>'error', 'text' => 'Name is too short or empty!'));
        die($output);
    }
    if(!filter_var($user_email, FILTER_VALIDATE_EMAIL)){ //email validation
        $output = json_encode(array('type'=>'error', 'text' => 'Please enter a valid email!'));
        die($output);
    }
    if(strlen($phone_number)>0 && !filter_var($phone_number, FILTER_SANITIZE_NUMBER_FLOAT)){ //check for valid numbers in phone number field
        $output = json_encode(array('type'=>'error', 'text' => 'Enter only digits in phone number'));
        die($output);
    }
    if(strlen($message)<3){ //check emtpy message
        $output = json_encode(array('type'=>'error', 'text' => 'Too short message! Please enter something.'));
        die($output);
    }
    
    //email body
    $message_body = $message."\r\n\r\n".$user_name."\r\nEmail : ".$user_email."\r\nPhone Number : ". $phone_number ;
    
    // Identify the mail server, username, password, and port
    $server   = getenv("EMAIL_SERVER");
    $username = getenv("EMAIL_USERNAME");
    $password = getenv("EMAIL_PASSWORD");
    $port     = getenv("EMAIL_PORT");

    // Set up the mail headers
    $headers = array(
        "From"      => $username,
        "To"        => $to_email,
        "Subject"   => $user_name.'-'.$phone_number.' : '.$subject
    );

    // Configure the mailer mechanism
    $smtp = Mail::factory("smtp",
        array(
            "host"     => $server,
            "username" => $username,
            "password" => $password,
            "auth"     => true,
            "port"     => $port
        )
    );

    // Send the message
    $mail = $smtp->send($to_email, $headers, $message_body);

    if( $copyme == "on" )
    {
        $headers = array(
            "From"      => $username,
            "To"        => $user_email,
            "Subject"   => 'CC: '.$subject
        );
        $mail = $smtp->send($user_email, $headers, $message_body);
    }
    
    if(PEAR::isError($mail))
    {
        //If mail couldn't be sent output error. Check your PHP email configuration (if it ever happens)
        $output = json_encode(
            array(
                'type'=>'error', 
                'text' => 'Could not send mail! '.$mail->getMessage() 
                )
            );
        die($output);
    }else{
        $output = json_encode(
            array(
                'type'=>'message', 
                'text' => 'Hi '.$user_name .'! Thank you for your email.'
                )
            );
        die($output);
    }
}
?>