<?php
header('Content-Type: application/json');
include "../conn.php";
session_start();
$id = $_SESSION['tournament_code'];

$request = $_REQUEST;
$columns = ['m.match_id','team1','team2','m.start_date','m.end_date','m.user_id','p.fullname','m.status'];

$sql = "SELECT m.match_id,a1.name AS team1 ,a2.name AS team2,m.start_date,m.end_date,m.user_id,p.fullname,m.status FROM tbl_matches AS m
        LEFT JOIN tbl_team AS team1 ON m.team1=team1.team_id
        LEFT JOIN tbl_association AS a1 ON team1.ass_id=a1.ass_id
        LEFT JOIN tbl_team AS team2 ON m.team2=team2.team_id
        LEFT JOIN tbl_association AS a2 ON team2.ass_id=a2.ass_id
        LEFT JOIN tbl_profile AS p ON m.user_id=p.user_id";

$where = " WHERE m.tourna_id='$id' AND m.status !='2'";

if (!empty($request['search']['value'])) {
    $search = $con->real_escape_string($request['search']['value']);
    $where .= " AND (m.match_id LIKE '%$search%' OR a1.name LIKE '%$search%' OR a2.name LIKE '%$search%' OR p.fullname LIKE '%$search%')";
}

$totalQuery = $con->query("SELECT COUNT(*) as total FROM tbl_matches WHERE tourna_id='$id'");
$totalData = $totalQuery->fetch_assoc()['total'];

$filteredQuery = $con->query("SELECT COUNT(*) as total FROM tbl_matches AS m
        LEFT JOIN tbl_team AS team1 ON m.team1=team1.team_id
        LEFT JOIN tbl_association AS a1 ON team1.ass_id=a1.ass_id
        LEFT JOIN tbl_team AS team2 ON m.team2=team2.team_id
        LEFT JOIN tbl_association AS a2 ON team2.ass_id=a2.ass_id
        LEFT JOIN tbl_profile AS p ON m.user_id=p.user_id
        $where");
$totalFiltered = $filteredQuery->fetch_assoc()['total'];

$columnIndex = $request['order'][0]['column'] ?? 0;
$columnOrder = $columns[$columnIndex] ?? 'm.match_id';
$orderDir = strtoupper($request['order'][0]['dir']) == 'ASC' ? 'ASC' : 'DESC';

$start = intval($request['start']) ?? 0;
$length = intval($request['length']) ?? 10;

$sql .= $where . " ORDER BY $columnOrder $orderDir LIMIT $start, $length";

$query = $con->query($sql);
$data = [];

while ($row = $query->fetch_assoc()) {
    $row['start_date'] = ($row['start_date'] != null) ? date('M j, Y h:i A', strtotime($row['start_date'])) : "";
    $row['end_date'] = ($row['end_date'] != null) ? date('M j, Y h:i A', strtotime($row['end_date'])): "";
    $row['status'] = $row['status'] != 0 ? ($row['status'] == 1 ? "Ongoing" : "Game Concluded") : "Not Started";
    $data[] = $row;
}

echo json_encode([
    "draw" => intval($request['draw']),
    "recordsTotal" => intval($totalData),
    "recordsFiltered" => intval($totalFiltered),
    "data" => $data
]);
?>
