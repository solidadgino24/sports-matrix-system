<?php  
include("../conn.php");
session_start();

$ev_id = $_SESSION['ev_id'] ?? 0;
$tableId = $_GET['table'] ?? 'overall-medal';

// Fetch event info
$event_sql = $con->prepare("SELECT * FROM tbl_event WHERE ev_id=?");
$event_sql->bind_param("i", $ev_id);
$event_sql->execute();
$event_result = $event_sql->get_result();
$event = $event_result->fetch_assoc();
$event_sql->close();

// Fetch associations
$assoc_sql = $con->query("SELECT ass_id, name FROM tbl_association ORDER BY name ASC");
$associations = [];
while($row = mysqli_fetch_assoc($assoc_sql)){
    $associations[$row['ass_id']] = $row['name'];
}

// Fetch tournaments
$tourn_sql = $con->query("
    SELECT t.tourna_id, s.name AS sport_name, gm.players, gm.name AS game_mode_name,
           gmc.category AS category
    FROM tbl_tournament t
    LEFT JOIN tbl_game_modes gm ON t.game_id = gm.game_id
    LEFT JOIN tbl_sports s ON gm.sport_id = s.sport_id
    LEFT JOIN tbl_game_mode_cat gmc ON gm.gm_cat_id = gmc.gm_cat_id
    WHERE t.ev_id='$ev_id'
");
$tournaments = [];
while($row = mysqli_fetch_assoc($tourn_sql)){
    $mode = ($row['players'] >= 3) ? "Team" : (($row['players']==2) ? "Doubles" : "Individual");
    $row['mode'] = $mode;
    $row['tournament_name'] = "{$row['sport_name']} – {$mode} ({$row['category']})";
    $tournaments[$row['tourna_id']] = $row;
}

// Prepare overall medal tally
$overall_medals = [];
foreach($associations as $ass_id => $ass_name){
    $overall_medals[$ass_id] = ["gold"=>0,"silver"=>0,"bronze"=>0,"placer"=>0,"dq"=>0];
}

// Determine report type and content
$tableTitle = "";
$reportType = "";
$medalData = [];

if($tableId == 'overall-medal'){
    $tableTitle = "🏆 Overall Medal Tally";
    $reportType = "Medal Tallies Report";

    foreach($tournaments as $tourn){
        foreach($associations as $ass_id => $ass_name){
            $stmt = $con->prepare("SELECT place, disqualify FROM tbl_team WHERE tourna_id=? AND ass_id=?");
            $stmt->bind_param("ii", $tourn['tourna_id'], $ass_id);
            $stmt->execute();
            $res = $stmt->get_result();
            while($team = mysqli_fetch_assoc($res)){
                if($team['disqualify']==1) $overall_medals[$ass_id]['dq']++;
                else{
                    if(!is_null($team['place'])){
                        if($team['place']==0) $overall_medals[$ass_id]['gold']++;
                        elseif($team['place']==1) $overall_medals[$ass_id]['silver']++;
                        elseif($team['place']==2) $overall_medals[$ass_id]['bronze']++;
                        elseif($team['place']>2) $overall_medals[$ass_id]['placer']++;
                    }
                }
            }
            $stmt->close();
        }
    }
    $medalData = $overall_medals;

} else {
    // Tournament-specific report
    $tourna_id = str_replace('tourna-', '', $tableId);
    if(isset($tournaments[$tourna_id])){
        $tourn = $tournaments[$tourna_id];
        $tableTitle = $tourn['tournament_name'];
        $reportType = "Match Summary Report";

        foreach($associations as $ass_id => $ass_name){
            $medals = ["gold"=>0,"silver"=>0,"bronze"=>0,"placer"=>0,"dq"=>0];
            $stmt = $con->prepare("SELECT place, disqualify FROM tbl_team WHERE tourna_id=? AND ass_id=?");
            $stmt->bind_param("ii", $tourna_id, $ass_id);
            $stmt->execute();
            $res = $stmt->get_result();
            while($team = mysqli_fetch_assoc($res)){
                if($team['disqualify']==1) $medals['dq']++;
                else{
                    if(!is_null($team['place'])){
                        if($team['place']==0) $medals['gold']++;
                        elseif($team['place']==1) $medals['silver']++;
                        elseif($team['place']==2) $medals['bronze']++;
                        elseif($team['place']>2) $medals['placer']++;
                    }
                }
            }
            $stmt->close();
            $medalData[$ass_id] = $medals;
        }
    } else {
        die("Tournament not found.");
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($tableTitle) ?> | Print</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { 
  font-family: "Times New Roman", Georgia, serif; 
  background: #fff; 
  margin: 0; 
  padding: 0;
}
.header-section {
  text-align: center;
  margin-bottom: 20px;
}
.header-section h1 {
  font-size: 22px;
  font-weight: bold;
  margin-bottom: 5px;
}
.header-section h2 {
  font-size: 18px;
  color: #333;
  margin-bottom: 15px;
}
.header-section p {
  font-size: 14px;
  margin: 2px 0;
  color: #444;
}
.table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 10px;
}
.table th, .table td {
  border: 1px solid #000;
  padding: 8px;
  font-size: 14px;
  text-align: center;
}
.table thead th {
  background: #e9ecef;
  font-weight: 700;
}
.medal-gold { color: #FFD700; font-weight: bold; }
.medal-silver { color: #C0C0C0; font-weight: bold; }
.medal-bronze { color: #CD7F32; font-weight: bold; }
.medal-placer { color: #007BFF; }
.medal-dq { color: #FF0000; font-weight: bold; }

@media print {
  body { background: #fff; padding: 0; margin: 0; }
  .no-print { display: none !important; }
}
</style>
</head>
<body>

<div class="header-section">
    <h1><?= htmlspecialchars($event['ev_name']) ?></h1>
    <h2><?= htmlspecialchars($reportType) ?></h2>
    <p><strong>Description:</strong> <?= htmlspecialchars($event['ev_description']) ?></p>
    <p><strong>Duration:</strong> <?= date("F j, Y", strtotime($event['start'])) ?> — <?= date("F j, Y", strtotime($event['end'])) ?></p>
    <p><strong>Location:</strong> <?= htmlspecialchars($event['ev_address']) ?></p>
</div>

<h3 style="text-align:center; margin-bottom:10px;"><?= htmlspecialchars($tableTitle) ?></h3>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>College</th>
            <th>Gold 🥇</th>
            <th>Silver 🥈</th>
            <th>Bronze 🥉</th>
            <th>Placer</th>
            <th>Disqualified</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($medalData as $ass_id => $medals): ?>
        <tr>
            <td><?= htmlspecialchars($associations[$ass_id]) ?></td>
            <td class="medal-gold"><?= $medals['gold'] ?></td>
            <td class="medal-silver"><?= $medals['silver'] ?></td>
            <td class="medal-bronze"><?= $medals['bronze'] ?></td>
            <td class="medal-placer"><?= $medals['placer'] ?></td>
            <td class="medal-dq"><?= $medals['dq'] ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<script>
window.onload = function(){
    window.print();
    window.onafterprint = function(){ window.close(); };
};
</script>

</body>
</html>
