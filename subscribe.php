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
    $user_email     = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
    
    if(!filter_var($user_email, FILTER_VALIDATE_EMAIL)){ //email validation
        $output = json_encode(array('type'=>'error', 'text' => 'Please enter a valid email!'));
        die($output);
    }
    
    // Identify the mail server, username, password, and port
    $server   = getenv("EMAIL_SERVER");
    $username = getenv("EMAIL_USERNAME");
    $password = getenv("EMAIL_PASSWORD");
    $port     = getenv("EMAIL_PORT");

    // Set up the mail headers
    $headers = array(
        "From"      => $username,
        "To"        => $user_email,
        "Subject"   => "Merci de votre abonnement."
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
    $mail = $smtp->send($user_email, $headers, "Vous recevrez régulièrement des emails vous donnant des conseils de jardinage.");

    $headers = array(
        "From"      => $username,
        "To"        => $to_email,
        "Subject"   => 'Nouvel abonnement :'.$user_email
    );
    $mail = $smtp->send($to_email, $headers, "Yo! Un nouvel abonné à la newsletter :)");
    
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
                'text' => 'Vous êtes bien abonné à la newsletter, merci.'
                )
            );
        die($output);
    }
}
?>