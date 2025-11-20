<?php
include("../conn.php");
session_start();

// Get logged in association ID from session
$ass_id = $_SESSION['ass_id'] ?? 0;
if(!$ass_id) die("Unauthorized access.");

// Fetch association name
$ass_sql = $con->prepare("SELECT name FROM tbl_association WHERE ass_id=?");
$ass_sql->bind_param("i", $ass_id);
$ass_sql->execute();
$ass_result = $ass_sql->get_result();
$association = $ass_result->fetch_assoc();
$ass_sql->close();

// Fetch active event
$event_sql = $con->query("SELECT * FROM tbl_event WHERE ev_status = 1 LIMIT 1");
$event = $event_sql->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($association['name']) ?> | Team Reports</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
html, body {
  background: #fff;
  font-family: "Times New Roman", serif;
  margin: 0 !important;
  padding: 0 !important;
  width: 100%;
  height: 100%;
  overflow-x: hidden;
}
.report-wrapper {
  width: 100%;
  max-width: 100%;
  margin: 0;
  padding: 20px 40px;
}
.report-header {
  text-align: center;
  margin-bottom: 25px;
}
.report-header h2 {
  font-weight: bold;
  color: #003366;
  text-transform: uppercase;
  margin-bottom: 5px;
}
.report-header h4 {
  margin-top: 0;
  color: #555;
}
.meta {
  text-align: center;
  margin-bottom: 20px;
  font-size: 15px;
}
.table-formal {
  width: 100%;
  border-collapse: collapse;
  margin-top: 10px;
}
.table-formal th, .table-formal td {
  border: 1px solid #000;
  padding: 8px;
  font-size: 14px;
  text-align: center;
}
.table-formal thead th {
  background: #e9ecef;
  font-weight: bold;
}
.tournament-section {
  margin-bottom: 40px;
  border-top: 2px solid #003366;
  padding-top: 15px;
}
.section-controls {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
  margin-bottom: 10px;
}
.section-controls button {
  font-size: 13px;
}
@media print {
  .no-print { display: none !important; }
  html, body { margin: 0 !important; padding: 0 !important; }
  .report-wrapper { padding: 0 20px !important; border: none; box-shadow: none; }
}
</style>
</head>
<body>

<div class="report-wrapper">
  <div class="report-header">
    <h2>College of <?= htmlspecialchars($association['name']) ?></h2>
    <h4>Team Players Report</h4>
  </div>

  <div class="meta">
    <strong>Event:</strong> <?= htmlspecialchars($event['ev_name'] ?? 'N/A') ?><br>
    <strong>Duration:</strong> <?= htmlspecialchars($event['start'] ?? '') ?> - <?= htmlspecialchars($event['end'] ?? '') ?><br>
    <strong>Location:</strong> <?= htmlspecialchars($event['ev_address'] ?? '') ?>
  </div>

  <hr>

  <?php
  $team_sql = $con->prepare("
    SELECT t.team_id, tr.tourna_id, s.name AS sport_name, gm.name AS game_mode, gmc.category AS category
    FROM tbl_team t
    LEFT JOIN tbl_tournament tr ON t.tourna_id = tr.tourna_id
    LEFT JOIN tbl_game_modes gm ON tr.game_id = gm.game_id
    LEFT JOIN tbl_sports s ON gm.sport_id = s.sport_id
    LEFT JOIN tbl_game_mode_cat gmc ON gm.gm_cat_id = gmc.gm_cat_id
    WHERE t.ass_id = ?
    ORDER BY s.name ASC
  ");
  $team_sql->bind_param("i", $ass_id);
  $team_sql->execute();
  $teams = $team_sql->get_result();

  if ($teams->num_rows > 0) {
    while ($team = $teams->fetch_assoc()) {
      $teamId = $team['team_id'];
  ?>
  <div class="tournament-section" id="tournament_<?= $teamId ?>">
    <div class="section-controls no-print">
      <button class="btn btn-sm btn-secondary" onclick="printSection('tournament_<?= $teamId ?>')">🖨️ Print</button>
      <button class="btn btn-sm btn-danger" onclick="downloadPDF('tournament_<?= $teamId ?>', '<?= htmlspecialchars($team['sport_name']) ?>')">📄 Download PDF</button>
    </div>

    <h5><strong>Sport:</strong> <?= htmlspecialchars($team['sport_name']) ?></h5>
    <p><strong>Game Mode:</strong> <?= htmlspecialchars($team['game_mode'] ?? '') ?> (<?= htmlspecialchars($team['category'] ?? '') ?>)</p>

    <?php
      $players_sql = $con->prepare("
        SELECT p.fullname, p.gender, TIMESTAMPDIFF(YEAR, p.birthday, CURDATE()) AS age,
               p.contact, p.email, ta.jersey_number, ta.date_added
        FROM tbl_team_players tp
        LEFT JOIN tbl_tourna_application ta ON tp.app_id = ta.app_id
        LEFT JOIN tbl_profile p ON ta.prof_id = p.prof_id
        WHERE tp.team_id = ?
      ");
      $players_sql->bind_param("i", $teamId);
      $players_sql->execute();
      $players = $players_sql->get_result();

      if ($players->num_rows > 0) {
        echo "<div class='table-responsive'>";
        echo "<table class='table-formal'><thead><tr>
                <th>Player Name</th>
                <th>Jersey No.</th>
                <th>Gender</th>
                <th>Age</th>
                <th>Contact</th>
                <th>Email</th>
                <th>Date Added</th>
              </tr></thead><tbody>";

        while ($p = $players->fetch_assoc()) {
          echo "<tr>
                  <td>" . htmlspecialchars($p['fullname']) . "</td>
                  <td>" . htmlspecialchars($p['jersey_number']) . "</td>
                  <td>" . ($p['gender'] == 1 ? 'Male' : 'Female') . "</td>
                  <td>" . htmlspecialchars($p['age']) . "</td>
                  <td>" . htmlspecialchars($p['contact']) . "</td>
                  <td>" . htmlspecialchars($p['email']) . "</td>
                  <td>" . htmlspecialchars(date('M d, Y', strtotime($p['date_added']))) . "</td>
                </tr>";
        }

        echo "</tbody></table></div>";
      } else {
        echo "<p>No registered players for this team.</p>";
      }
    ?>
  </div>
  <?php
    }
  } else {
    echo "<p class='text-center'>No teams found for this association.</p>";
  }
  ?>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>
function printSection(id) {
  const section = document.getElementById(id).cloneNode(true);
  const noPrintElements = section.querySelectorAll('.no-print');
  noPrintElements.forEach(el => el.remove());

  const printWindow = window.open('', '', 'width=900,height=1000');
  printWindow.document.write(`
    <html>
      <head>
        <title>Print Report</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
          body { font-family: "Times New Roman", serif; margin: 0; padding: 20px; }
          table { border-collapse: collapse; width: 100%; }
          th, td { border: 1px solid #000; padding: 6px; text-align: center; font-size: 14px; }
          th { background-color: #f2f2f2; font-weight: bold; }
          h5 { margin-top: 15px; }
        </style>
      </head>
      <body>${section.outerHTML}</body>
    </html>
  `);
  printWindow.document.close();
  printWindow.focus();
  printWindow.print();
}

async function downloadPDF(sectionId, title) {
  const { jsPDF } = window.jspdf;
  const section = document.getElementById(sectionId);
  const pdf = new jsPDF({ orientation: "portrait", unit: "pt", format: "a4" });

  let y = 60;
  pdf.setFont("times", "bold");
  pdf.setFontSize(16);
  pdf.text("Team Players Report - " + title, 40, y);
  y += 20;
  pdf.setFont("times", "normal");
  pdf.setFontSize(12);

  const table = section.querySelector("table");
  if (!table) return alert("No player data available.");

  const rows = Array.from(table.querySelectorAll("tbody tr"));
  const headers = Array.from(table.querySelectorAll("thead th")).map(th => th.textContent.trim());

  pdf.setFont("times", "bold");
  pdf.text(headers.join(" | "), 40, y);
  y += 15;
  pdf.setFont("times", "normal");

  rows.forEach(row => {
    const cols = Array.from(row.querySelectorAll("td")).map(td => td.textContent.trim());
    pdf.text(cols.join(" | "), 40, y);
    y += 15;
    if (y > 770) { pdf.addPage(); y = 60; }
  });

  pdf.save(`${title.replace(/\s+/g, '_')}_team_report.pdf`);
}
</script>

</body>
</html>
