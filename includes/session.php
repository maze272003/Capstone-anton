<?php
// Secure session settings (Set before session_start)
session_set_cookie_params([
    'lifetime' => 0,    // Session lasts until the browser closes
    'path' => '/',
    'domain' => '',     // Set to your domain if needed
    'secure' => isset($_SERVER['HTTPS']), // Requires HTTPS
    'httponly' => true, // Prevents JavaScript access
    'samesite' => 'Strict' // Helps prevent CSRF attacks
]);

session_start(); // Start session after setting cookie parameters

class Session {
    public $msg;
    private $user_is_logged_in = false;

    function __construct() {
        $this->flash_msg();
        $this->userLoginSetup();
    }

    public function isUserLoggedIn() {
        return $this->user_is_logged_in;
    }

    public function login($user_id) {
        session_regenerate_id(true); // Prevent session fixation
        $_SESSION['user_id'] = $user_id;
        $_SESSION['user_ip'] = $_SERVER['REMOTE_ADDR'];
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
    }

    private function userLoginSetup() {
        if (isset($_SESSION['user_id'])) {
            if ($_SESSION['user_ip'] !== $_SERVER['REMOTE_ADDR'] || 
                $_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
                $this->logout();
                return;
            }
            $this->user_is_logged_in = true;
        } else {
            $this->user_is_logged_in = false;
        }
    }

    public function logout() {
        $_SESSION = [];
        session_unset();
        session_destroy();

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, 
                $params["path"], $params["domain"], 
                $params["secure"], $params["httponly"]
            );
        }
    }

    public function msg($type = '', $msg = '') {
        if (!empty($msg)) {
            if (strlen(trim($type)) == 1) {
                $type = str_replace(['d', 'i', 'w', 's'], 
                                    ['danger', 'info', 'warning', 'success'], 
                                    $type);
            }
            $_SESSION['msg'][$type] = $msg;
        } else {
            return $this->msg;
        }
    }

    private function flash_msg() {
        if (isset($_SESSION['msg'])) {
            $this->msg = $_SESSION['msg'];
            unset($_SESSION['msg']);
        } else {
            $this->msg;
        }
    }
}

$session = new Session();
$msg = $session->msg();
?>

