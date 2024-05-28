<?php
session_start();
include 'db-connect.php';

$results_per_page = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$starting_limit_number = ($page-1) * $results_per_page;

$sql = "SELECT ticket_id, task_name, description, status, assigned_to, date_created FROM ticketing_table LIMIT $starting_limit_number, $results_per_page";
$result = $conn->query($sql);

$tickets = [];
while ($row = $result->fetch_assoc()) {
    $tickets[] = $row;
}

$total_results = $conn->query("SELECT COUNT(ticket_id) AS total FROM ticketing_table")->fetch_assoc()['total'];
$total_pages = ceil($total_results / $results_per_page);

echo json_encode(['tickets' => $tickets, 'total_pages' => $total_pages]);
?>
