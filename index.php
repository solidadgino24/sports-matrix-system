<?php 
session_start();
include("conn.php");

$event = $con->query("SELECT * FROM tbl_event WHERE ev_status=1");

if(mysqli_num_rows($event) <= 0){
  $event = null;
}else{
  $event = mysqli_fetch_assoc($event);
  $_SESSION['ev_id'] = $event['ev_id'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CHMSU-A Sport Matrix</title>
<link rel="icon" href="icons/ico.png">
<script src="js/jquery.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
:root {
  --white: #fff;
  --black: #222;
  --gray: #555;
  --light-gray: #f6f6f6;
  --green: hsl(145, 58%, 45%);
  --green-dark: hsl(145, 60%, 35%);
  --green-light: hsl(145, 60%, 70%);
  --live: hsl(0, 75%, 50%);
}

/* Reset */
* {margin:0;padding:0;box-sizing:border-box;}
body {
  font-family:'Segoe UI',sans-serif;
  background:linear-gradient(to bottom right,#f9fdf9,#f3fdf5);
  color:var(--black);
  min-height:100vh;
}

/* Background */
.background-blur {
  position:fixed;
  inset:0;
  background:url('chmsubg.jpg') center/cover no-repeat;
  filter:blur(10px) brightness(0.9);
  z-index:-2;
}
body::before {
  content:"";
  position:fixed;
  inset:0;
  background:rgba(255,255,255,0.85);
  z-index:-1;
}

/* Navbar */
.navbar {
  display:flex;
  justify-content:space-between;
  align-items:center;
  background:linear-gradient(90deg,var(--green),var(--green-dark));
  color:#fff;
  padding:12px 24px;
  position:sticky;
  top:0;
  z-index:10;
}
.navbar .logo {
  display:flex;
  align-items:center;
  gap:10px;
}
.logo img {width:40px;height:40px;border-radius:50%;border:2px solid #fff;}
.logo span {font-weight:600;font-size:20px;}
.login-btn {
  background:#fff;
  color:var(--green-dark);
  border:none;
  padding:8px 18px;
  border-radius:25px;
  cursor:pointer;
  font-weight:600;
  transition:0.3s;
}
.login-btn:hover {background:hsl(0,0%,95%);transform:translateY(-2px);}

/* Container */
.container {max-width:1100px;margin:auto;padding:30px 20px 80px;}

/* Event header */
/* Hero header */
.header {
  backdrop-filter: blur(12px);
  background: hsla(0, 0%, 100%, 0.75);
  border: 1px solid var(--accent);
  border-radius: 20px;
  padding: 35px 30px;
  margin: 30px auto;
  width: min(700px, 95%);
  text-align: center;
  box-shadow: 0 12px 35px -10px rgba(0,0,0,0.08);
  transition: 0.3s;
}

.header:hover {
  transform: scale(1.01);
}

.header h1 {
  font-size: clamp(24px, 2.5vw, 32px);
  color: var(--green-dark);
  margin-bottom: 8px;
}

.header p {
  color: var(--gray);
  font-size: 15px;
}

.header span.ev-start {
  font-weight: 600;
  color: var(--green);
}

.header span.ev-end {
  font-weight: 600;
  color: var(--gray);
}

.header.no-event h1 {
  color: hsl(0, 70%, 50%);
}
.header.no-event p {
  color: #777;
  font-size: 14px;
}
/* Chart Section */
.chart-container {
  position: relative;
  width: 100%;
  max-width: 100%;
  height: 400px;
  background: var(--white);
  border-radius: 16px;
  box-shadow: 0 6px 20px rgba(0,0,0,0.05);
  padding: 20px;
  margin: 40px auto;
  overflow-x: auto; /* allow horizontal scrolling on very small screens */
}

.chart-container::before {
  content: "Medal Standings";
  position: absolute;
  top: 12px;
  left: 20px;
  font-size: 16px;
  font-weight: 600;
  color: var(--green-dark);
}

/* Tournament list */
.tournament-list {
  display:flex;
  flex-wrap:wrap;
  justify-content:center;
  gap:12px;
  margin:30px 0;
}
.tournament-item {
  background:linear-gradient(135deg,#f9fef9,#e9fbea);
  border:1px solid #dfe9df;
  border-radius:10px;
  padding:10px 18px;
  color:var(--green-dark);
  cursor:pointer;
  transition:0.3s;
  font-weight:500;
}
.tournament-item:hover {
  background:var(--green);
  color:#fff;
  transform:translateY(-3px);
}
a {
  text-decoration: none;
}

/* Collapsible tournament block */
.event {
  background:#fff;
  border-radius:12px;
  padding:22px;
  margin-bottom:20px;
  border-left:5px solid var(--green);
  box-shadow:0 4px 10px rgba(0,0,0,0.05);
}
.event h2 {
  color:var(--green-dark);
  font-size:18px;
  margin-bottom:8px;
}
.event .tournament {
  color:var(--gray);
  font-weight:500;
  font-size:14px;
  margin-bottom:10px;
}
.event .matches {
  display:none;
  margin-top:15px;
  animation:fadeInUp 0.4s ease;
}

/* Matches */
.match {
  background:#fafafa;
  border:1px solid #eee;
  border-radius:10px;
  padding:14px 18px;
  margin-bottom:10px;
  display:flex;
  justify-content:space-between;
  align-items:center;
  flex-wrap:wrap;
}
.match:hover {background:#f2fef2;}
.match-details {font-weight:600;}
.match-time {font-size:13px;color:var(--gray);}
.match-status {font-size:14px;font-weight:600;}
.live {color:var(--live);}
.live::before {
  content:"";
  width:8px;height:8px;
  background:var(--live);
  border-radius:50%;
  display:inline-block;
  margin-right:6px;
  animation:pulse 1s infinite;
}

/* Buttons */
.upcoming-btn {
  background:var(--green);
  color:#fff;
  border:none;
  border-radius:6px;
  padding:6px 14px;
  font-size:14px;
  cursor:pointer;
  transition:0.3s;
}
.upcoming-btn:hover {background:var(--green-dark);}
.upcoming-btn:disabled {background:var(--green-light);cursor:not-allowed;}

/* Scroll to top */
#scrollTopBtn {
  display:none;
  position:fixed;
  bottom:25px;
  right:25px;
  z-index:99;
  font-size:20px;
  border:none;
  background:var(--green);
  color:#fff;
  cursor:pointer;
  padding:12px 16px;
  border-radius:50%;
  box-shadow:0 4px 10px rgba(0,0,0,0.25);
  transition:all 0.3s ease;
}
#scrollTopBtn:hover {background:var(--green-dark);transform:translateY(-3px);}

/* Animations */
@keyframes fadeInUp {from{opacity:0;transform:translateY(10px);}to{opacity:1;transform:translateY(0);}}
@keyframes pulse {0%{box-shadow:0 0 0 0 rgba(255,0,0,0.5);}70%{box-shadow:0 0 0 10px rgba(255,0,0,0);}100%{box-shadow:0 0 0 0 rgba(255,0,0,0);}}

/* Responsive */
@media (max-width:768px) {
  .navbar {flex-direction:column;align-items:flex-start;gap:8px;}
  .match {flex-direction:column;align-items:flex-start;}
  .upcoming-btn {width:100%;margin-top:6px;}
  .chart-container {
    height: 320px;
    padding: 15px;
  }

  .chart-container::before {
    font-size: 14px;
  }
}
@media (max-width: 480px) {
  .chart-container {
    height: 280px;
    padding: 10px;
  }
  .chart-container::before {
    font-size: 10px;
  }

  canvas {
    min-width: 500px; /* ensures bars don't squeeze too tightly */
  }
  .ranking-box {
    padding: 10px;
  }

  .ranking-title {
    font-size: 16px;
  }

  .ranking-item span {
    font-size: 13px;
  }

  .navbar .logo span {
    font-size: 16px;
  }

  .login-btn {
    padding: 6px 14px;
    font-size: 14px;
  }
}
.ranking-box {
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.05);
  padding: 15px 20px;
  margin: 15px 0;
}

.ranking-title {
  color: #222;
  font-size: 18px;
  font-weight: 600;
  margin-bottom: 10px;
}

.ranking-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.ranking-item {
  display: flex;
  justify-content: space-between;
  background: #f9f9f9;
  border: 1px solid #eee;
  border-left: 6px solid transparent;
  border-radius: 8px;
  padding: 8px 12px;
  transition: all 0.2s ease;
}

.ranking-item:hover {
  transform: translateY(-2px);
  box-shadow: 0 3px 8px rgba(0,0,0,0.05);
}

.ranking-item.gold { border-left-color: #f1c40f; }
.ranking-item.silver { border-left-color: #bdc3c7; }
.ranking-item.bronze { border-left-color: #e67e22; }
.ranking-item.other { border-left-color: #27ae60; }

</style>
</head>
<body>
<div class="background-blur"></div>

<div class="navbar">
  <div class="logo">
    <img src="icons/ico.png" alt="Logo">
    <span>CHMSU-A Sport Matrix</span>
  </div>
  <a href="login.php"><button class="login-btn">Login</button></a>
</div>

<div class="container">
  <div class="header <?= $event ? '' : 'no-event' ?>">
    <?php if ($event): ?>
      <h1><?= $event['ev_name'] ?></h1>
      <p><?= $event['ev_description'] ?></p>
      <p><span class="ev-start">Start: <?= date("F j, Y", strtotime($event['start'])) ?></span> — 
         <span class="ev-end">End: <?= date("F j, Y", strtotime($event['end'])) ?></span></p>
      <p style="font-size:14px;">Location: <span><?= $event['ev_address'] ?></span></p>
    <?php else: ?>
      <h1>No Active Event</h1>
      <p>There are no ongoing tournaments at the moment.</p>
    <?php endif; ?>
  </div>

<?php 
        if($event && $event['ev_remarks'] != 0){
    ?>
    <div class="col-md-12 chart-container">
        <canvas id="medalChart"></canvas>
        <div class="clearfix"></div>
    </div>   
    <?php    
        }
    ?>
    <div class="tournament-list">
<?php 
if ($event) {
  $ev_id = $event['ev_id'];
  $tournaments = [];

  $tourna = $con->query("
      SELECT 
          t.tourna_id, 
          s.name AS sport_name, 
          gm.players, 
          gm.name AS game_mode_name,
          gmc.category AS category,
          t.status
      FROM tbl_tournament AS t
      LEFT JOIN tbl_game_modes AS gm ON t.game_id = gm.game_id
      LEFT JOIN tbl_sports AS s ON gm.sport_id = s.sport_id
      LEFT JOIN tbl_game_mode_cat AS gmc ON gm.gm_cat_id = gmc.gm_cat_id
      WHERE t.ev_id = '$ev_id'
  ");

  while($row = mysqli_fetch_assoc($tourna)){
      // Determine Game Mode
      if ($row['players'] >= 3) $mode = "Team";
      elseif ($row['players'] == 2) $mode = "Doubles";
      else $mode = "Individual";

      $tournament_name = "{$row['sport_name']}–{$mode}({$row['category']})";
      $row['mode'] = $mode;
      $row['tournament_name'] = $tournament_name;
      $tournaments[] = $row;
?>
  <a href="#<?= $row['tourna_id'] ?>">
      <div class="tournament-item"><?= $tournament_name ?></div>
  </a>
<?php } } ?>
</div>

<?php 
if (!empty($tournaments)) {
foreach ($tournaments as $value) {
?>
<div id="<?= $value['tourna_id'] ?>" class="event">
    <h2>Featured Sport: <?= $value['sport_name'] ?></h2>
    <div class="tournament">Tournament: <?= $value['sport_name'] ?>–<?= $value['mode'] ?>(<?= $value['category'] ?>)</div>

    <?php 
    $tourna_id = $value['tourna_id'];
    
// ✅ Get tournament status directly
$tourna_status_query = $con->query("SELECT status FROM tbl_tournament WHERE tourna_id='$tourna_id'");
$tourna_status = mysqli_fetch_assoc($tourna_status_query);
$tourna_status = $tourna_status ? (int)$tourna_status['status'] : 0;

// ✅ Check if matches exist
$match_check = $con->query("SELECT COUNT(*) AS total FROM tbl_matches WHERE tourna_id='$tourna_id'");
$match_check = mysqli_fetch_assoc($match_check);

if ($match_check['total'] == 0) {
    echo "<p style='color:#777;font-size:14px;'>Tournament not started yet.</p>";
} else {
    // ✅ Calculate wins/losses dynamically
    $standings_sql = $con->query("
        SELECT 
            t.team_id,
            a.name AS team_name,
            SUM(CASE WHEN m.winner = t.team_id THEN 1 ELSE 0 END) AS wins,
            SUM(CASE WHEN m.winner IS NOT NULL AND m.winner != t.team_id THEN 1 ELSE 0 END) AS losses
        FROM tbl_team AS t
        LEFT JOIN tbl_association AS a ON t.ass_id = a.ass_id
        LEFT JOIN tbl_matches AS m 
            ON (t.team_id = m.team1 OR t.team_id = m.team2)
        WHERE t.tourna_id = '$tourna_id'
        GROUP BY t.team_id, a.name
    ");

    if (mysqli_num_rows($standings_sql) > 0) {
        $teams = [];
        while ($row = mysqli_fetch_assoc($standings_sql)) {
            $teams[] = $row;
        }

        // ✅ Sort standings
        usort($teams, function($a, $b){
            if ($a['wins'] == $b['wins']) return $a['losses'] <=> $b['losses'];
            return $b['wins'] <=> $a['wins'];
        });

        // ✅ Detect if tournament has no remaining ongoing matches
        $ongoing_check = $con->query("
            SELECT COUNT(*) AS ongoing 
            FROM tbl_matches 
            WHERE tourna_id='$tourna_id' 
              AND (status != '2' AND status != 'cancelled')
        ");
        $ongoing = mysqli_fetch_assoc($ongoing_check)['ongoing'];

        // ✅ Determine if truly ended
        $isEnded = ($tourna_status == 2 && $ongoing == 0);

        $rankingTitle = $isEnded ? "🏆 Final Rankings" : "📊 Current Standings";

        echo "<hr><div class='ranking-box'><h3 class='ranking-title'>{$rankingTitle}</h3><div class='ranking-list'>";

        $rank = 1;
        $prevWins = $prevLosses = null;
        $tieRank = 1;

        foreach ($teams as $t) {
            $wins = (int)$t['wins'];
            $losses = (int)$t['losses'];

            if ($prevWins === $wins && $prevLosses === $losses) {
                // same standing for tied teams
            } else {
                $tieRank = $rank;
            }

            // Ordinal suffix
            $suffix = "th";
            if (!in_array(($tieRank % 100), [11, 12, 13])) {
                $last = $tieRank % 10;
                if ($last == 1) $suffix = "st";
                elseif ($last == 2) $suffix = "nd";
                elseif ($last == 3) $suffix = "rd";
            }

            // ✅ Determine label based on status
            if ($wins == 0 && $losses == 0) {
    $placement = "No records yet";
    $cls = "other";
} else {
    if ($isEnded) {
        switch($tieRank){
            case 1: 
                $placement = "🥇 Champion"; 
                $cls = "gold"; 
                break;
            case 2: 
                $placement = "🥈 1st Place"; 
                $cls = "silver"; 
                break;
            case 3: 
                $placement = "🥉 2nd Place"; 
                $cls = "bronze"; 
                break;
            case 4:
                $placement = "3rd Place"; 
                $cls = "other"; 
                break;
            default: 
                $placement = ($tieRank-1)."th Place"; 
                $cls = "other"; 
        }
    } else {
        $placement = "{$tieRank}{$suffix} Place";
        $cls = "other";
    }
}


            echo "
            <div class='ranking-item {$cls}'>
                <strong>{$placement}</strong>
                <span>".htmlspecialchars($t['team_name'])." — {$wins}W / {$losses}L</span>
            </div>";

            $prevWins = $wins;
            $prevLosses = $losses;
            $rank++;
        }

        echo "</div></div>";
    } else {
        echo "<p style='color:#777;font-size:14px;'>No standings available yet.</p>";
    }
}
?>


    <!-- Matches -->
    <div class="matches-box">
        <h3 style="margin-top:20px; color:var(--green-dark); font-size:18px;">Matches</h3>
        <?php
        $match_sql = $con->query("
            SELECT m.match_id,a1.name AS teamA,a2.name AS teamB,
                   m.status,m.winner,m.team1,m.team2,m.start_date
            FROM tbl_matches AS m 
            LEFT JOIN tbl_team AS t1 ON m.team1=t1.team_id 
            LEFT JOIN tbl_association AS a1 ON t1.ass_id=a1.ass_id 
            LEFT JOIN tbl_team AS t2 ON m.team2=t2.team_id 
            LEFT JOIN tbl_association AS a2 ON t2.ass_id=a2.ass_id 
            WHERE m.tourna_id='{$value['tourna_id']}' 
            ORDER BY m.match_id DESC
        ");

        if (mysqli_num_rows($match_sql) > 0) {
            while ($row = mysqli_fetch_assoc($match_sql)) {
                if($row['status'] == 0){ ?>
                <div class="match">
                    <div class="match-details"><?= $row['teamA'] ?> vs <?= $row['teamB'] ?></div>
                    <div class="match-time"><?= isset($row['start_date']) ? date("F j, Y - g:i A", strtotime($row['start_date'])) : "" ?></div>
                    <div class="match-status">Status: Upcoming</div>
                    <button class="upcoming-btn" disabled>View Match</button>
                </div>
        <?php } else {
            $winner = ($row['winner'] == $row['team1']) ? $row['teamA'] : $row['teamB']; ?>
                <div class="match" data-id="<?= $row['match_id'] ?>">
                    <div class="match-details"><?= $row['teamA'] ?> vs <?= $row['teamB'] ?></div>
                    <div class="match-time"><?= isset($row['start_date']) ? date("F j, Y - g:i A", strtotime($row['start_date'])) : "" ?></div>
                    <div class="match-status <?= ($row['status'] == 1) ? 'live' : '' ?>">
                        <?= ($row['status'] == 1) ? 'Status: Live' : 'Winner: '.$winner ?>
                    </div>
                    <button class="upcoming-btn">View Match</button>
                </div>
        <?php } } } else {
            echo "<p style='color:#777;font-size:14px;'>No matches available yet.</p>";
        } ?>
    </div>
</div>
<?php } } ?>

<!-- Scroll to Top -->
<button id="scrollTopBtn">↑</button>

<script>
$(function(){
  $(window).scroll(()=> $(this).scrollTop()>300 ? $("#scrollTopBtn").fadeIn() : $("#scrollTopBtn").fadeOut());
  $("#scrollTopBtn").click(()=> $("html,body").animate({scrollTop:0},600));

  $(document).on("click",".upcoming-btn:not(.toggle-matches)",function(){
    let id = $(this).closest(".match").data("id");
    document.cookie = "match_id="+encodeURIComponent(id)+";path=/";
    window.location.href = "game/";
  });
});
<?php 
        if($event && $event['ev_remarks'] != 0){
    ?>
        const ctx = document.getElementById('medalChart').getContext('2d');
            $.get("list.php?s=medals", function(res) {
                console.log(res)
                if (res.status && Array.isArray(res.data)) {
                    const labels = [];
                    const goldData = [];
                    const silverData = [];
                    const bronzeData = [];
            
                    res.data.forEach(item => {
                        labels.push(item.name);
                        goldData.push(item.medals.gold);
                        silverData.push(item.medals.silver);
                        bronzeData.push(item.medals.bronze);
                    });
            
                    new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [
                                {
                                    label: '🥇 Gold',
                                    data: goldData,
                                    backgroundColor: '#f1c40f',
                                    borderRadius: 8,
                                    barThickness: 25
                                },
                                {
                                    label: '🥈 Silver',
                                    data: silverData,
                                    backgroundColor: '#bdc3c7',
                                    borderRadius: 8,
                                    barThickness: 25
                                },
                                {
                                    label: '🥉 Bronze',
                                    data: bronzeData,
                                    backgroundColor: '#e67e22',
                                    borderRadius: 8,
                                    barThickness: 25
                                }
                            ]
                        },
                        options: {
  responsive: true,
  maintainAspectRatio: false,
  interaction: {
    mode: 'index',
    intersect: false
  },
  plugins: {
    legend: {
      position: 'top',
      labels: {
        usePointStyle: true,
        pointStyle: 'rectRounded',
        padding: 10,
        font: {
          size: window.innerWidth < 480 ? 10 : 13,
          weight: 'bold'
        }
      }
    },
    tooltip: {
      backgroundColor: '#333',
      titleColor: '#fff',
      bodyColor: '#fff',
      borderColor: '#fff',
      borderWidth: 1,
      padding: 10,
      cornerRadius: 6,
      callbacks: {
        label: function(context) {
          return `${context.dataset.label}: ${Math.round(context.parsed.y)}`;
        }
      }
    }
  },
  scales: {
    x: {
      grid: { display: false },
      ticks: {
        font: {
          size: window.innerWidth < 480 ? 9 : 12,
          weight: '600'
        },
        maxRotation: 45,
        minRotation: 0
      }
    },
    y: {
      beginAtZero: true,
      grid: {
        color: '#eee',
        borderDash: [5, 5]
      },
      title: {
        display: true,
        text: 'No. of Events Won',
        font: {
          size: window.innerWidth < 480 ? 11 : 14
        }
      },
      ticks: {
        stepSize: 1,
        callback: function(value) {
          return value % 1 === 0 ? value : '';
        }
      }
    }
  }
}

                    });
                } else {
                    console.warn("No medal data found or invalid response.");
                }
            });
    <?php
        }
    ?>

</script>
</body>
</html>
