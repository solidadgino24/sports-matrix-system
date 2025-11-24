<?php
include("../conn.php");
session_start();
$ev_id = $_SESSION['ev_id'] ?? 0;

// Fetch event details
$event = ['ev_name'=>'(No event selected)', 'ev_description'=>'', 'start'=>'', 'end'=>'', 'ev_address'=>''];
if($ev_id){
    $stmt = $con->prepare("SELECT ev_name, ev_description, start, `end`, ev_address FROM tbl_event WHERE ev_id = ?");
    $stmt->bind_param("i", $ev_id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    if($res) $event = $res;
    $stmt->close();
}

// Associations
$associations = [];
$res = $con->query("SELECT ass_id, name FROM tbl_association");
while($r = $res->fetch_assoc()) $associations[$r['ass_id']] = $r['name'];

// Overall medals
$overall_medals = [];
foreach($associations as $aid => $name){
    $overall_medals[$aid] = ['gold'=>0,'silver'=>0,'bronze'=>0,'placer'=>0,'dq'=>0];
}
$sql = $con->query("SELECT name, ass_id FROM tbl_association");

// Tournaments
$tq = $con->prepare("\n    SELECT t.tourna_id, t.status, gm.players, s.name AS sport_name, gmc.category\n    FROM tbl_tournament t\n    LEFT JOIN tbl_game_modes gm ON t.game_id = gm.game_id\n    LEFT JOIN tbl_sports s ON gm.sport_id = s.sport_id\n    LEFT JOIN tbl_game_mode_cat gmc ON gm.gm_cat_id = gmc.gm_cat_id\n    WHERE t.ev_id = ?\n    ORDER BY s.name, gmc.category\n");
$tq->bind_param("i", $ev_id);
$tq->execute();
$tournaments = $tq->get_result()->fetch_all(MYSQLI_ASSOC);

// Medal aggregation
foreach($tournaments as $t){
    $stmt = $con->prepare("SELECT ass_id, place, disqualify FROM tbl_team WHERE tourna_id = ?");
    $stmt->bind_param("i", $t['tourna_id']);
    $stmt->execute();
    $res = $stmt->get_result();
    while($tr = $res->fetch_assoc()){
        $aid = $tr['ass_id'];
        if($tr['disqualify']==1) $overall_medals[$aid]['dq']++;
        else {
            $p = (int)$tr['place'];
            if($p===0) $overall_medals[$aid]['gold']++;
            elseif($p===1) $overall_medals[$aid]['silver']++;
            elseif($p===2) $overall_medals[$aid]['bronze']++;
            elseif($p>2) $overall_medals[$aid]['placer']++;
        }
    }
    $stmt->close();
}
$tq->close();

function h($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

// Helper: ordinal label for quarters
function qt_label($n){
    if($n==1) return '1st QT';
    if($n==2) return '2nd QT';
    if($n==3) return '3rd QT';
    if($n==4) return '4th QT';
    // Overtimes
    $ot_index = $n - 4;
    return $ot_index==1 ? 'OT' : 'OT'.$ot_index;
}

// Helper: ordinal for sets
function set_label($n){
    if($n==1) return '1st Set';
    if($n==2) return '2nd Set';
    if($n==3) return '3rd Set';
    return $n.'th Set';
}

?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Reports — <?= h($event['ev_name']) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<style>
body { font-family: "Times New Roman", Georgia, serif; background:#f5f5f5; margin:0; padding:0; }
.report-wrapper { width:100%; margin:0; padding:10px 10px; }
.report-card { background:#fff; padding:20px; border:1px solid #ddd; margin-bottom:25px; box-sizing:border-box; border-radius:6px; }
.report-title { text-align:center; font-size:20px; font-weight:700; margin-bottom:6px; }
.report-sub { text-align:center; margin-bottom:18px; color:#444; }
.meta-row { display:flex; justify-content:space-between; flex-wrap:wrap; margin-bottom:12px; color:#222; font-size:14px; }
.table-formal { width:100%; border-collapse:collapse; margin-top:10px; }
.table-formal th, .table-formal td { border:1px solid #000; padding:8px; font-size:14px; text-align:center; }
.table-formal thead th { background:#e9ecef; font-weight:700; }
.section-controls { display:flex; flex-wrap:wrap; justify-content:flex-end; gap:8px; margin-bottom:10px; }
.table-responsive { overflow-x:auto; }
.no-print {}
@media (max-width: 768px) { .meta-row div { width:100%; margin-bottom:8px; font-size:13px; } .report-card { padding:14px; } .report-title { font-size:18px; } .report-sub { font-size:14px; } .table-formal th, .table-formal td { font-size:12px; padding:5px; } .section-controls { justify-content:center; } }
@media print { body { background:#fff; padding:0; margin:0; } .no-print { display:none !important; } .report-wrapper { padding:0; margin:0; width:100%; } .report-card { border:none; box-shadow:none; padding:0 0 12px 0; margin:0 0 20px 0; } @page { size:A4; margin:10mm; } }
/* Scroll to Top Button */
#scrollTopBtn {
    display: none;
    position: fixed;
    bottom: 25px;
    right: 25px;
    z-index: 9999;
    font-size: 20px;
    border: none;
    background: #27ae60;
    color: #fff;
    cursor: pointer;
    padding: 12px 16px;
    border-radius: 50%;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.25);
    transition: all 0.3s ease;
    font-weight: bold;
}

#scrollTopBtn:hover {
    background: #229954;
    transform: translateY(-3px);
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.3);
}

#scrollTopBtn:active {
    transform: translateY(-1px);
}

@media print {
    #scrollTopBtn {
        display: none !important;
    }
}
</style>
</head>
<body>
<div class="report-wrapper">

  <!-- Header -->
  <div class="report-card" id="report-header">
    <div class="report-title">OFFICIAL REPORT — <?= h($event['ev_name']) ?></div>
    <div class="report-sub"><?= h($event['ev_description']) ?></div>
    <div class="meta-row">
      <div><strong>Event Dates:</strong> <?= h($event['start'] ? date("F j, Y", strtotime($event['start'])) : '') ?> — <?= h($event['end'] ? date("F j, Y", strtotime($event['end'])) : '') ?></div>
      <div><strong>Location:</strong> <?= h($event['ev_address']) ?></div>
    </div>
  </div>

  <!-- Controls -->
  <div class="report-card no-print text-center">
    <div class="d-flex justify-content-center align-items-center flex-wrap gap-2 gap-md-3">

      <select id="reportSelector" class="form-select w-auto">
        <option value="medal-tally-section" selected>Overall Medal Tallies</option>
        <option value="official-results-section">Official Rankings</option>
        <option value="match-summary-section">Match Summary Reports</option>
      </select>
      <button id="printActive" class="btn btn-outline-secondary btn-sm">Print</button>
      <button id="pdfActive" class="btn btn-outline-danger btn-sm">Download PDF</button>
    </div>
  </div>

  <!-- Medal Tallies -->
  <div class="report-card" id="medal-tally-section">
    <h4>Overall Medal Tallies</h4>
    <div class="table-responsive">
      <table class="table-formal">
        <thead>
          <tr><th>College</th><th>Gold</th><th>Silver</th><th>Bronze</th><th>Placer</th><th>Disqualified</th></tr>
        </thead>
        <tbody>
          <?php
                while ($row = mysqli_fetch_assoc($sql)) {
                    $id = $row['ass_id'];
                    $medals = ["gold" => 0, "silver" => 0, "bronze" => 0, "placer" => 0, "dq" => 0,];
                    $team = $con->query("SELECT * FROM tbl_team t LEFT JOIN tbl_tournament AS tn ON t.tourna_id = tn.tourna_id WHERE ass_id = '$id' AND tn.ev_id = '$ev_id'");

                    while ($teamRow = mysqli_fetch_assoc($team)) {
                        if($teamRow['disqualify'] == 1){
                            $medals['dq']++;
                        }else{
                            if($teamRow['place'] !== null){
                                if ($teamRow['place'] == 0) {
                                    $medals['gold']++;
                                } elseif ($teamRow['place'] == 1) {
                                    $medals['silver']++;
                                } elseif ($teamRow['place'] == 2) {
                                    $medals['bronze']++;
                                } elseif ($teamRow['place'] > 2) {
                                    $medals['placer']++;
                                }
                            }
                        }
                    }
            ?>
            <tr>
                <td><?php echo htmlspecialchars($row['name']) ?></td>
                <td><?php echo $medals['gold'] ?></td>
                <td><?php echo $medals['silver'] ?></td>
                <td><?php echo $medals['bronze'] ?></td>
                <td><?php echo $medals['placer'] ?></td>
                <td><?php echo $medals['dq'] ?></td>
            </tr>
            <?php } ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Official Results -->
  <div class="report-card" id="official-results-section">
    <h4>Official Rankings</h4>
    <table class="table-formal">
      <thead>
        <tr><th>Sport</th><th>Category</th><th>Champion</th><th>1st Place</th><th>2nd Place</th><th>3rd Place</th></tr>
      </thead>
      <tbody>
      <?php
      foreach($tournaments as $t){
        $teams = [];
        $q = $con->prepare("SELECT ass_id, place FROM tbl_team WHERE tourna_id=? ORDER BY place ASC");
        $q->bind_param("i",$t['tourna_id']);
        $q->execute();
        $res = $q->get_result();
        while($r=$res->fetch_assoc()) $teams[$r['place']] = $associations[$r['ass_id']] ?? 'Unknown';
        $q->close();
        echo "<tr>\n          <td>".h($t['sport_name'])."</td>\n          <td>".h($t['category'])."</td>\n          <td>".h($teams[0] ?? '—')."</td>\n          <td>".h($teams[1] ?? '—')."</td>\n          <td>".h($teams[2] ?? '—')."</td>\n          <td>".h($teams[3] ?? '—')."</td>\n        </tr>";
      }
      ?>
      </tbody>
    </table>
  </div>

<div class="report-card" id="match-summary-section">
    <h4>Match Summary Reports</h4>
    
    <!-- Tournament Filter Dropdown -->
    <div class="no-print" style="margin-bottom: 20px;">
      <label for="tournamentFilter" style="font-weight: 700; margin-right: 10px;">Filter by Tournament:</label>
      <select id="tournamentFilter" class="form-select w-auto d-inline-block" style="width: auto;">
        <option value="">-- All Tournaments --</option>
        <?php 
        foreach($tournaments as $t){
          $sport_cat = h($t['sport_name']) . ($t['category'] ? " ({$t['category']})" : "");
          echo "<option value='tournament-{$t['tourna_id']}'>$sport_cat</option>";
        }
        ?>
      </select>
    </div>

    <!-- Matches Container -->
    <div id="matches-container">
  <?php foreach($tournaments as $t): 
      $status = ($t['status']==0) ? 'Not Started' : (($t['status']==1) ? 'Ongoing' : 'Ended');
      $winnerText = '';
      if($t['status']==2){
          $wt = $con->prepare("SELECT ass_id FROM tbl_team WHERE tourna_id = ? AND place = 0 LIMIT 1");
          $wt->bind_param("i",$t['tourna_id']);
          $wt->execute();
          $res = $wt->get_result()->fetch_assoc();
          if($res) $winnerText = $associations[$res['ass_id']] ?? '';
          $wt->close();
      }

      // Determine max number of sets/quarters for this tournament
      $max_sq_q = 0;
      $ms = $con->prepare("SELECT MAX(set_quarter) AS mx FROM tbl_score_match WHERE match_id IN (SELECT match_id FROM tbl_matches WHERE tourna_id = ?)");
      $ms->bind_param("i", $t['tourna_id']);
      $ms->execute();
      $mxr = $ms->get_result()->fetch_assoc();
      if($mxr && $mxr['mx']) $max_sq_q = (int)$mxr['mx'];
      $ms->close();

      if($max_sq_q == 0) $max_sq_q = 4;
      
      $isBasketball = stripos($t['sport_name'], 'basket') !== false;
      
      // Get all matches for this tournament
      $mq = $con->prepare("SELECT m.match_id, m.team1, m.team2, m.winner, m.end_date, m.status FROM tbl_matches m WHERE m.tourna_id = ? ORDER BY m.match_id ASC");
      $mq->bind_param("i",$t['tourna_id']);
      $mq->execute();
      $mres = $mq->get_result();
      
      if($mres->num_rows > 0){
          while($mr = $mres->fetch_assoc()){
              $match_id = $mr['match_id'];
              
              $team1 = $con->query("SELECT ass_id FROM tbl_team WHERE team_id={$mr['team1']} LIMIT 1")->fetch_assoc();
              $team2 = $con->query("SELECT ass_id FROM tbl_team WHERE team_id={$mr['team2']} LIMIT 1")->fetch_assoc();

              $team1_name = $associations[$team1['ass_id'] ?? 0] ?? 'Team 1';
              $team2_name = $associations[$team2['ass_id'] ?? 0] ?? 'Team 2';

              // Fetch per-quarter/set scores
              $sq = $con->prepare("SELECT set_quarter, team1, team2 FROM tbl_score_match WHERE match_id = ? ORDER BY set_quarter ASC");
              $sq->bind_param("i", $match_id);
              $sq->execute();
              $sres = $sq->get_result();
              $scores_map = [];
              $final_t1 = 0; $final_t2 = 0; $sets_won_1 = 0; $sets_won_2 = 0;
              $last_quarter = 0;

              while($sr = $sres->fetch_assoc()){
                  $sqn = (int)$sr['set_quarter'];
                  $t1_score = (int)$sr['team1'];
                  $t2_score = (int)$sr['team2'];
                  $scores_map[$sqn] = ['t1'=>$t1_score, 't2'=>$t2_score];
                  
                  if($sqn > $last_quarter){
                      $last_quarter = $sqn;
                      $final_t1 = $t1_score;
                      $final_t2 = $t2_score;
                  }
                  
                  if($t1_score > $t2_score) $sets_won_1++; 
                  elseif($t2_score > $t1_score) $sets_won_2++;
              }
              $sq->close();

              // Final score representation
              if($isBasketball){
                  $final_score = $final_t1 . ' - ' . $final_t2;
              } else {
                  $final_score = $sets_won_1 . '-' . $sets_won_2;
              }

              // Determine winner display
              $match_status = (int)($mr['status'] ?? 0);

              if ($match_status === 2) {
                  if (!empty($mr['winner'])) {
                      $w = $con->query("SELECT ass_id FROM tbl_team WHERE team_id={$mr['winner']} LIMIT 1")->fetch_assoc();
                      $winner_display = $associations[$w['ass_id'] ?? 0] ?? 'Winner';
                  } else {
                      if($isBasketball){
                          $winner_display = ($final_t1 > $final_t2) ? $team1_name : (($final_t2 > $final_t1) ? $team2_name : 'Draw');
                      } else {
                          if($sets_won_1 > $sets_won_2) $winner_display = $team1_name;
                          elseif($sets_won_2 > $sets_won_1) $winner_display = $team2_name;
                          else $winner_display = 'Draw';
                      }
                  }
                  $ended = !empty($mr['end_date']) ? date("M d, Y h:i A", strtotime($mr['end_date'])) : "—";
              } else {
                  $winner_display = "Ongoing";
                  $ended = "—";
              }

              // Individual Match Report Card
              $reportType = h($t['sport_name']) . ($t['category'] ? " ({$t['category']})" : "") . " - " . h($team1_name . " vs " . $team2_name);
  ?>
  <div class="report-card match-card" id="match-<?= (int)$match_id ?>" data-tournament-id="tournament-<?= (int)$t['tourna_id'] ?>" data-report-type="<?= $reportType ?>">
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
      <div>
        <div style="font-weight:700; display:flex; align-items:center; gap:15px; flex-wrap:wrap;">
          <!-- Team 1 with color coding -->
          <div style="padding:8px 12px; border-radius:4px; <?php 
            if($match_status === 2) {
              echo ($winner_display === $team1_name) ? 'background:#d4edda; color:#155724; border:1px solid #28a745;' : 'background:#f8d7da; color:#721c24; border:1px solid #f5c6cb;';
            }
          ?>">
            <?= h($team1_name) ?>
          </div>
          
          <!-- VS Badge -->
          <div style="font-weight:bold; font-size:16px; color:#666;">V.S.</div>
          
          <!-- Team 2 with color coding -->
          <div style="padding:8px 12px; border-radius:4px; <?php 
            if($match_status === 2) {
              echo ($winner_display === $team2_name) ? 'background:#d4edda; color:#155724; border:1px solid #28a745;' : 'background:#f8d7da; color:#721c24; border:1px solid #f5c6cb;';
            }
          ?>">
            <?= h($team2_name) ?>
          </div>
        </div>
        
        <div style="font-size:13px;color:#333; margin-top:10px;">
          Sport: <strong><?= h($t['sport_name']) ?></strong>
          <?= $t['category'] ? " | Category: <strong>" . h($t['category']) . "</strong>" : "" ?>
          | Final Score: <strong><?= h($final_score) ?></strong>
          | Winner: <strong><?= h($winner_display) ?></strong>
          | Date Ended: <strong><?= h($ended) ?></strong>
        </div>
      </div>
      <div class="section-controls no-print">
        <button class="btn btn-outline-secondary btn-sm print-section">Print</button>
        <button class="btn btn-outline-danger btn-sm pdf-section">Download PDF</button>
      </div>
    </div>

<!-- Quarter/Set Scores Table -->
    <div class="table-responsive" style="margin-bottom: 20px;">
      <table class="table-formal">
        <thead>
          <tr>
            <th>Team</th>
            <?php
            // Only display quarters/sets that have scores
            for($i=1;$i<=$max_sq_q;$i++){
                if(isset($scores_map[$i])){
                    echo "<th>" . ($isBasketball ? h(qt_label($i)) : h(set_label($i)) ) . "</th>\n";
                }
            }
            echo "<th>Total Score</th>";
            ?>
          </tr>
        </thead>
        <tbody>
          <tr style="<?php 
            if($match_status === 2 && $winner_display === $team1_name) {
              echo 'background:#d4edda;';
            } elseif($match_status === 2 && $winner_display !== $team1_name && $winner_display !== 'Ongoing' && $winner_display !== 'Draw') {
              echo 'background:#f8d7da;';
            }
          ?>">
            <td style="text-align:left;"><?= h($team1_name) ?></td>
            <?php
            for($i=1;$i<=$max_sq_q;$i++){
                if(isset($scores_map[$i])){
                    echo "<td>".h($scores_map[$i]['t1'])."</td>\n";
                }
            }
            echo "<td><strong>".h($final_t1)."</strong></td>\n";
            ?>
          </tr>
          <tr style="<?php 
            if($match_status === 2 && $winner_display === $team2_name) {
              echo 'background:#d4edda;';
            } elseif($match_status === 2 && $winner_display !== $team2_name && $winner_display !== 'Ongoing' && $winner_display !== 'Draw') {
              echo 'background:#f8d7da;';
            }
          ?>">
            <td style="text-align:left;"><?= h($team2_name) ?></td>
            <?php
            for($i=1;$i<=$max_sq_q;$i++){
                if(isset($scores_map[$i])){
                    echo "<td>".h($scores_map[$i]['t2'])."</td>\n";
                }
            }
            echo "<td><strong>".h($final_t2)."</strong></td>\n";
            ?>
          </tr>
        </tbody>
      </table>
    </div>

  </div>
  <?php
          }
      }
      $mq->close();
  ?>
  <?php endforeach; ?>
    </div>
</div>
<!-- Scroll to Top Button -->
<button id="scrollTopBtn" class="no-print" title="Go to top">↑</button>
<script>
$(document).ready(function() {
  function showSelectedReport() {
    const selected = $("#reportSelector").val();
    $(".report-card[id$='-section']").hide();
    $("#" + selected).show();
  }

  $("#reportSelector").on("change", showSelectedReport);
  showSelectedReport();
});

async function exportElementToPDF(element, filename, reportType) {
  try {
    const clone = element.cloneNode(true);
    $(clone).find('.no-print').remove();
    const wrapper = document.createElement('div');
    wrapper.style.position = 'fixed'; wrapper.style.left = '-9999px'; wrapper.style.top = '0'; wrapper.style.width = '794px'; wrapper.style.background = '#fff'; wrapper.style.padding = '20px'; wrapper.style.boxSizing = 'border-box';
    wrapper.appendChild(clone);
    document.body.appendChild(wrapper);

    const headerHTML = `\n      <div style="text-align:center;font-family:'Times New Roman',serif;margin-bottom:12px;">\n        <h3 style="margin:0;font-weight:bold;">${reportType} — <?= h($event['ev_name']) ?></h3>\n        <div style="font-size:14px;"><?= h($event['ev_description']) ?></div>\n        <div style="font-size:13px;"><?= h($event['ev_address']) ?></div>\n      </div>`;
    clone.insertAdjacentHTML("afterbegin", headerHTML);
    await new Promise(r => setTimeout(r, 250));

    const canvas = await html2canvas(wrapper, { scale: window.devicePixelRatio > 2 ? 2 : 3, useCORS: true, backgroundColor: '#ffffff' });
    document.body.removeChild(wrapper);

    const { jsPDF } = window.jspdf;
    const pdf = new jsPDF('p', 'pt', 'a4');
    const pdfWidth = pdf.internal.pageSize.getWidth();
    const pdfHeight = pdf.internal.pageSize.getHeight();
    const imgWidth = pdfWidth - 40;
    const imgHeight = (canvas.height * imgWidth) / canvas.width;

    let position = 0;
    while (position < canvas.height) {
      const pageCanvas = document.createElement('canvas');
      const pageHeightPx = (canvas.width * (pdfHeight - 40)) / pdfWidth;
      pageCanvas.width = canvas.width;
      pageCanvas.height = Math.min(pageHeightPx, canvas.height - position);
      const pageCtx = pageCanvas.getContext('2d');
      pageCtx.drawImage(canvas, 0, position, canvas.width, pageCanvas.height, 0, 0, canvas.width, pageCanvas.height);

      const imgData = pageCanvas.toDataURL('image/png');
      pdf.addImage(imgData, 'PNG', 20, 20, imgWidth, (pageCanvas.height * imgWidth) / canvas.width);

      position += pageHeightPx;
      if (position < canvas.height) pdf.addPage();
    }

    pdf.save(filename);
  } catch (e) {
    console.error(e);
    alert("PDF export failed. Check console for details.");
  }
}

$(document).on("click", ".pdf-section", function(e) {
  e.preventDefault();
  const card = $(this).closest(".report-card")[0];
  const reportType = $(card).data("report-type") || "Report";
  const safeEvent = <?= json_encode(preg_replace("/[^a-z0-9]+/i","_",strtolower($event['ev_name']))); ?>;
  const filename = reportType.replace(/[^a-z0-9]/gi,'_').toLowerCase() + '_' + safeEvent + '.pdf';
  exportElementToPDF(card, filename, reportType);
});

$(document).on("click", ".print-section", function(e) {
  e.preventDefault();
  const card = $(this).closest(".report-card")[0];
  const reportType = $(card).dataset.reportType || "Report";
  const clone = card.cloneNode(true);
  $(clone).find(".no-print").remove();

  const w = window.open("", "_blank", "width=900,height=700");
  const html = `\n  <html><head>\n    <title>${reportType} — <?= h($event['ev_name']) ?></title>\n    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">\n    <style> body { font-family:"Times New Roman",serif; padding:20px; } table { border-collapse:collapse; width:100%; font-size:13px; } th,td { border:1px solid #000; padding:6px; text-align:center; } @page { size: A4; margin: 10mm; } </style>\n  </head><body>\n    <div style="text-align:center;margin-bottom:10px;">\n      <h3 style="margin:0;font-weight:bold;">${reportType} — <?= h($event['ev_name']) ?></h3>\n      <div style="font-size:14px;"><?= h($event['ev_description']) ?></div>\n      <div style="font-size:13px;"><?= h($event['ev_address']) ?></div>\n    </div>\n    ${clone.outerHTML}\n  </body></html>`;
  w.document.open();
  w.document.write(html);
  w.document.close();
  w.onload = function(){ w.print(); setTimeout(()=>w.close(), 500); };
});

$("#printActive").click(function() {
  const selected = $("#reportSelector").val();
  const card = $("#" + selected)[0];
  const reportType = $("#" + selected + " h4").first().text() || "Report";

  const clone = card.cloneNode(true);
  $(clone).find(".no-print").remove();

  const w = window.open("", "_blank", "width=900,height=700");
  const html = `\n  <html><head>\n    <title>${reportType} — <?= h($event['ev_name']) ?></title>\n    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">\n    <style> body { font-family:"Times New Roman",serif; padding:20px; } table { border-collapse:collapse; width:100%; font-size:13px; } th,td { border:1px solid #000; padding:6px; text-align:center; } @page { size: A4; margin: 10mm; } </style>\n  </head><body>\n    <div style="text-align:center;margin-bottom:10px;">\n      <h3 style="margin:0;font-weight:bold;">${reportType} — <?= h($event['ev_name']) ?></h3>\n      <div style="font-size:14px;"><?= h($event['ev_description']) ?></div>\n      <div style="font-size:13px;"><?= h($event['ev_address']) ?></div>\n    </div>\n    ${clone.outerHTML}\n  </body></html>`;
  w.document.open();
  w.document.write(html);
  w.document.close();
  w.onload = function(){ w.print(); setTimeout(()=>w.close(), 500); };
});

$("#pdfActive").click(async function() {
  const selected = $("#reportSelector").val();
  const card = $("#" + selected)[0];
  const reportType = $("#" + selected + " h4").first().text() || "Report";
  const safeEvent = <?= json_encode(preg_replace("/[^a-z0-9]+/i","_",strtolower($event['ev_name']))); ?>;
  const filename = reportType.replace(/[^a-z0-9]/gi,'_').toLowerCase() + '_' + safeEvent + '.pdf';
  await exportElementToPDF(card, filename, reportType);
});
// Tournament Filter for Match Summary
$("#tournamentFilter").on("change", function() {
    const selectedTournament = $(this).val();
    
    if (selectedTournament === "") {
        // Show all matches
        $(".match-card").show();
    } else {
        // Hide all matches first
        $(".match-card").hide();
        // Show only matches from selected tournament
        $(".match-card[data-tournament-id='" + selectedTournament + "']").show();
    }
});

</script>
<script>
// Show/hide scroll to top button
window.addEventListener('scroll', function() {
    const scrollTopBtn = document.getElementById('scrollTopBtn');
    if (window.pageYOffset > 300) {
        scrollTopBtn.style.display = 'block';
    } else {
        scrollTopBtn.style.display = 'none';
    }
});

// Scroll to top on click
document.getElementById('scrollTopBtn').addEventListener('click', function() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
});
</script>
</body>
</html>
