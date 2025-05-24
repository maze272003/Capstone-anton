<?php
 $errors = array();

 /*--------------------------------------------------------------*/
 /* Function for Remove escapes special
 /* characters in a string for use in an SQL statement
 /*--------------------------------------------------------------*/
function real_escape($str){
  global $con;
  $escape = mysqli_real_escape_string($con,$str);
  return $escape;
}
/*--------------------------------------------------------------*/
/* Function for Remove html characters
/*--------------------------------------------------------------*/
function remove_junk($str){
  $str = nl2br($str);
  $str = htmlspecialchars(strip_tags($str, ENT_QUOTES));
  return $str;
}
/*--------------------------------------------------------------*/
/* Function for Uppercase first character
/*--------------------------------------------------------------*/
function first_character($str){
  $val = str_replace('-'," ",$str);
  $val = ucfirst($val);
  return $val;
}
/*--------------------------------------------------------------*/
/* Function for Checking input fields not empty
/*--------------------------------------------------------------*/
function validate_fields($var){
  global $errors;
  foreach ($var as $field) {
    $val = remove_junk($_POST[$field]);
    if(isset($val) && $val==''){
      $errors = $field ." can't be blank.";
      return $errors;
    }
  }
}
/*--------------------------------------------------------------*/
/* Function for Display Session Message
   Ex echo displayt_msg($message);
/*--------------------------------------------------------------*/
function display_msg($msg =''){
   $output = array();
   if(!empty($msg)) {
      foreach ($msg as $key => $value) {
         $output  = "<div class=\"alert alert-{$key}\">";
         $output .= "<a href=\"#\" class=\"close\" data-dismiss=\"alert\">&times;</a>";
         $output .= remove_junk(first_character($value));
         $output .= "</div>";
      }
      return $output;
   } else {
     return "" ;
   }
}
/*--------------------------------------------------------------*/
/* Function for redirect
/*--------------------------------------------------------------*/
function redirect($url, $permanent = false)
{
    if (headers_sent() === false)
    {
      header('Location: ' . $url, true, ($permanent === true) ? 301 : 302);
    }

    exit();
}
/*--------------------------------------------------------------*/
/* Function for find out total saleing price, buying price and profit
/*--------------------------------------------------------------*/
function total_price($totals){
   $sum = 0;
   $sub = 0;
   foreach($totals as $total ){
     $sum += $total['total_saleing_price'];
     $sub += $total['total_buying_price'];
     $profit = $sum - $sub;
   }
   return array($sum,$profit);
}
/*--------------------------------------------------------------*/
/* Function for Readable date time
/*--------------------------------------------------------------*/
function read_date($str){
     if($str)
      return date('F j, Y, g:i:s a', strtotime($str));
     else
      return null;
  }
/*--------------------------------------------------------------*/
/* Function for  Readable Make date time
/*--------------------------------------------------------------*/
function make_date(){
  return strftime("%Y-%m-%d %H:%M:%S", time());
}
/*--------------------------------------------------------------*/
/* Function for  Readable date time
/*--------------------------------------------------------------*/
function count_id(){
  static $count = 1;
  return $count++;
}
/*--------------------------------------------------------------*/
/* Function for Creting random string
/*--------------------------------------------------------------*/
function randString($length = 5)
{
  $str='';
  $cha = "0123456789abcdefghijklmnopqrstuvwxyz";

  for($x=0; $x<$length; $x++)
   $str .= $cha[mt_rand(0,strlen($cha))];
  return $str;
}

/* Categorized Bar Chart Function*/
function find_category_sales_summary() {
  global $db;
  $sql  = "SELECT c.name AS category, ";
  $sql .= "SUM(s.qty) AS total_qty, SUM(s.qty * s.price) AS total_sales ";
  $sql .= "FROM sales s ";
  $sql .= "JOIN products p ON s.product_id = p.id ";
  $sql .= "JOIN categories c ON p.categorie_id = c.id ";
  $sql .= "GROUP BY c.name";
  return find_by_sql($sql);
}


/*--------------------------------------------------------------*/
/* Function: Get items sold by month
/*--------------------------------------------------------------*/
function get_items_sold_by_month($year) {
  global $db;
  $sql = "SELECT MONTH(date) AS month, SUM(qty) AS total_qty 
          FROM sales 
          WHERE YEAR(date) = '{$db->escape($year)}' 
          GROUP BY MONTH(date)";
  return find_by_sql($sql);
}
/*--------------------------------------------------------------*/
/* Function: Get total items sold by hour
/*--------------------------------------------------------------*/
function get_items_sold_by_hour($date) {
  global $db;
  $sql = "SELECT HOUR(date) AS hour, SUM(qty) AS items_sold 
          FROM sales 
          WHERE DATE(date) = '{$db->escape($date)}' 
          GROUP BY HOUR(date)";
  return find_by_sql($sql);
}
/*--------------------------------------------------------------*/
/* Function: Get sales by hour
/*--------------------------------------------------------------*/
function get_sales_by_hour($date) {
  global $db;
  $sql = "SELECT HOUR(date) AS hour, SUM(qty * price) AS total_sales 
          FROM sales 
          WHERE DATE(date) = '{$db->escape($date)}' 
          GROUP BY HOUR(date)";
  return find_by_sql($sql);
}

function get_sales_by_day($month, $year) {
  global $db; // Changed from $con to $db

  $safe_month = $db->escape($month); // Using $db->escape()
  $safe_year = $db->escape($year); // Using $db->escape()

  $sql = "SELECT DAY(date) AS day, SUM(price * qty) AS total_sales
          FROM sales
          WHERE MONTH(date) = '{$safe_month}'
            AND YEAR(date) = '{$safe_year}'
          GROUP BY DAY(date)
          ORDER BY DAY(date)";

  $result = $db->query($sql); // Using $db->query()
  $data = [];

  if ($result) {
    while ($row = $db->fetch_assoc($result)) { // Using $db->fetch_assoc()
      $data[] = $row;
    }
  }

  return $data;
}
function authenticate_by_email($email, $password) {
    global $db;
    $email = $db->escape($email);
    $sql = "SELECT id, password FROM users WHERE email = '{$email}' LIMIT 1";
    $result = $db->query($sql);
    
    if($db->num_rows($result)) {
        $user = $db->fetch_assoc($result);
        $password_request = sha1($password);
        if($password_request === $user['password']){
            return $user['id'];
        }
    }
    return false;
}
?>
