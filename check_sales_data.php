<?php
require_once('includes/load.php');
page_require_level(3);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $start_date = isset($_POST['start_date']) ? $db->escape($_POST['start_date']) : '';
  $end_date = isset($_POST['end_date']) ? $db->escape($_POST['end_date']) : '';
  
  if (empty($start_date) || empty($end_date)) {
    echo json_encode(['has_data' => false]);
    exit;
  }
  
  // Query to check if data exists
  $sql = "SELECT COUNT(*) as total FROM sales WHERE date BETWEEN '{$start_date}' AND '{$end_date}'";
  $result = $db->query($sql);
  
  if ($result) {
    $data = $db->fetch_assoc($result);
    echo json_encode(['has_data' => ($data['total'] > 0)]);
  } else {
    echo json_encode(['has_data' => false]);
  }
} else {
  echo json_encode(['has_data' => false]);
}
?>