<?php include_once('includes/load.php'); ?>
<?php
$req_fields = array('username','password');
validate_fields($req_fields);
$username_or_email = remove_junk($_POST['username']);
$password = remove_junk($_POST['password']);

if(empty($errors)){
    // Check if the input is an email
    if(filter_var($username_or_email, FILTER_VALIDATE_EMAIL)) {
        // It's an email - authenticate by email
        $user_id = authenticate_by_email($username_or_email, $password);
    } else {
        // It's a username - authenticate by username
        $user_id = authenticate($username_or_email, $password);
    }
    
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