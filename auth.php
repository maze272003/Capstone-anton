<?php
include_once('includes/load.php');
$req_fields = array('username','password' );
validate_fields($req_fields);
$username = remove_junk($_POST['username']);
$password = remove_junk($_POST['password']);

if(empty($errors)){

    // reCAPTCHA verification
    $recaptcha_secret_key = '6Le-GFArAAAAAJCMTyB4iW-LAllMPPR1wWeWcIl5'; // Replace with your actual Secret Key
    $recaptcha_response = $_POST['g-recaptcha-response'];

    $verification_url = 'https://www.google.com/recaptcha/api/siteverify';
    $data = array(
        'secret' => $recaptcha_secret_key,
        'response' => $recaptcha_response
    );

    $options = array(
        'http' => array(
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data)
        )
    );

    $context  = stream_context_create($options);
    $result = file_get_contents($verification_url, false, $context);
    $recaptcha_result = json_decode($result, true);

    if (!$recaptcha_result['success']) {
        // reCAPTCHA verification failed
        $session->msg("d", "Please complete the reCAPTCHA.");
        redirect('index.php', false);
        exit(); // Stop script execution
    }

    // reCAPTCHA verification successful, proceed with authentication
    $user_id = authenticate($username, $password);
  if($user_id){
    //create session with id
     $session->login($user_id);
    //Update Sign in time
     updateLastLogIn($user_id);
     $session->msg("s", "Welcome to Inventory Management System");
     redirect('admin.php',false);

  } else {
    $session->msg("d", "Sorry Username/Password incorrect.");
    redirect('index.php',false);
  }

} else {
   $session->msg("d", $errors);
   redirect('index.php',false);
}

?>