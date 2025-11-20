<?php
header('Content-Type: application/json');
include "../conn.php";
session_start();
$id = $_SESSION['game_mode'];

$request = $_REQUEST;
$columns = ['g.game_id,g.name','g.players','c.category', 'g.scoring', 'g.point_base', 'g.sets', 'g.date_added'];

$sql = "SELECT g.*,c.category FROM tbl_game_modes AS g
        LEFT JOIN tbl_game_mode_cat AS c ON g.gm_cat_id=c.gm_cat_id";

$where = " WHERE g.sport_id='$id'";

if (!empty($request['search']['value'])) {
    $search = $con->real_escape_string($request['search']['value']);
    $where .= " AND (c.category LIKE '%$search%' OR g.name LIKE '%$search%' OR g.players LIKE '%$search%' OR g.sets LIKE '%$search%')";
}

$totalQuery = $con->query("SELECT COUNT(*) as total FROM tbl_game_modes");
$totalData = $totalQuery->fetch_assoc()['total'];

$filteredQuery = $con->query("SELECT COUNT(*) as total FROM tbl_game_modes AS g
        LEFT JOIN tbl_game_mode_cat AS c ON g.gm_cat_id=c.gm_cat_id
        $where");
$totalFiltered = $filteredQuery->fetch_assoc()['total'];

$columnIndex = $request['order'][0]['column'] ?? 0;
$columnOrder = $columns[$columnIndex] ?? 'g.date_added';
$orderDir = strtoupper($request['order'][0]['dir']) == 'ASC' ? 'ASC' : 'DESC';

$start = intval($request['start']) ?? 0;
$length = intval($request['length']) ?? 10;

$sql .= $where . " ORDER BY $columnOrder $orderDir LIMIT $start, $length";

$query = $con->query($sql);
$data = [];

while ($row = $query->fetch_assoc()) {
    $row['date_added'] = date('M j, Y h:i A', strtotime($row['date_added']));
    $row['scoring'] = ($row['scoring']==2)? "Sets" : "Quarter";
    $data[] = $row;
}

echo json_encode([
    "draw" => intval($request['draw']),
    "recordsTotal" => intval($totalData),
    "recordsFiltered" => intval($totalFiltered),
    "data" => $data
]);
?>
