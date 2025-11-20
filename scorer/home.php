<?php 
include "../conn.php";
session_start();
$ev_id = $_SESSION['ev_id'];

// 🟢 Count tournaments under event
$tournament = $con->query("SELECT COUNT(tourna_id) AS tournaments FROM tbl_tournament WHERE ev_id='$ev_id'");
$tournament = mysqli_fetch_assoc($tournament);

// 🟢 Count athletes under event
$athletes = $con->query("
    SELECT COUNT(tp.player_id) AS players 
    FROM tbl_team_players AS tp 
    LEFT JOIN tbl_team AS t ON tp.team_id = t.team_id 
    LEFT JOIN tbl_tournament AS tn ON t.tourna_id = tn.tourna_id 
    WHERE tn.ev_id = '$ev_id'
");
$athletes = mysqli_fetch_assoc($athletes);

// 🟢 Count all sports
$sport = $con->query("SELECT COUNT(sport_id) AS sports FROM tbl_sports");
$sport = mysqli_fetch_assoc($sport);


// ✅ Count tournaments marked completed (status = 2)
$tournament_done = $con->query("
    SELECT COUNT(tourna_id) AS tournaments 
    FROM tbl_tournament 
    WHERE ev_id = '$ev_id' 
    AND status = '2'
");
$tournament_done = mysqli_fetch_assoc($tournament_done);


// 🟢 Compute progress based on matches
$total_matches_sql = $con->query("
    SELECT COUNT(m.match_id) AS total 
    FROM tbl_matches AS m
    LEFT JOIN tbl_tournament AS t ON m.tourna_id = t.tourna_id
    WHERE t.ev_id = '$ev_id'
");
$total_matches = mysqli_fetch_assoc($total_matches_sql)['total'];

$concluded_matches_sql = $con->query("
    SELECT COUNT(m.match_id) AS concluded 
    FROM tbl_matches AS m
    LEFT JOIN tbl_tournament AS t ON m.tourna_id = t.tourna_id
    WHERE t.ev_id = '$ev_id' 
    AND m.status = '2'
");
$concluded_matches = mysqli_fetch_assoc($concluded_matches_sql)['concluded'];

$percentage = ($total_matches > 0) ? round(($concluded_matches / $total_matches) * 100, 2) : 0;

// 🟢 Get event details
$sql = $con->query("SELECT ev_name, ev_description FROM tbl_event WHERE ev_id='$ev_id'");
$event_details = mysqli_fetch_assoc($sql);
?>

<div class="main">
    <div class="body">
        <div class="card">
            <div class="bg-success text-white card-header" style="width:100%">
                <h3 class=""><i class="fa fa-bar-chart-o"></i> &nbsp;<?php echo $event_details['ev_name'] ?></h3>              
                <div class="progress progress-xs mt-3" style="height: 15px;">
                    <div class="progress-bar bg-warning" role="progressbar" 
                        style="width: <?php echo $percentage; ?>%;" 
                        aria-valuenow="<?php echo $percentage; ?>" 
                        aria-valuemin="0" aria-valuemax="100">
                    </div>
                </div>
                <center style="color:white;">Event Progress: <?php echo $percentage; ?>%</center>
            </div>

            <div class="card-body">
                <div class="col-md-4">
                    <div class="container mt-5" style="max-width: 400px;">
                        <div class="dashboard-box" style="background-color: #e74c3c;">
                            <div class="box-icon"><i class="fa fa-sitemap"></i></div>
                            <div class="box-content">
                                <h5 class="text-danger"><?php echo $tournament['tournaments']; ?></h5>
                                <small>NO. OF TOURNAMENTS</small>
                            </div>
                        </div>

                        <div class="dashboard-box" style="background-color: #17a2b8;">
                            <div class="box-icon"><i class="fa fa-users"></i></div>
                            <div class="box-content">
                                <h5 class="text-info"><?php echo $athletes['players']; ?></h5>
                                <small>NO. OF ATHLETES</small>
                            </div>
                        </div>

                        <div class="dashboard-box" style="background-color: #f1c40f;">
                            <div class="box-icon"><i class="fa fa-gamepad"></i></div>
                            <div class="box-content">
                                <h5 class="text-warning"><?php echo $sport['sports']; ?></h5>
                                <small>NO. OF SPORTS</small>
                            </div>
                        </div>

                        <div class="dashboard-box" style="background-color: #28a745;">
                            <div class="box-icon"><i class="fa fa-check"></i></div>
                            <div class="box-content">
                                <h5 class="text-success"><?php echo $tournament_done['tournaments']; ?></h5>
                                <small>COMPLETED TOURNAMENTS</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-8 chart-container">
                    <canvas id="medalChart"></canvas>
                    <div class="clearfix"></div>
                </div>        
            </div>
        <div class="clearfix"></div>
        </div>
    </div>
</div>

<style>
.head{
    display: flex;
    flex-direction: row;
    justify-content: space-between;
    align-items: center;
}
.card-header:first-child {
    border-radius: calc(.25rem - 1px) calc(.25rem - 1px) 0 0;
}
.text-white { color: #fff !important; }
.bg-success { background-color: #28a745 !important; }
.card { margin-bottom: 1.5rem; border-radius: 0; position: relative; display: flex; flex-direction: column; min-width: 0; background-color: #fff; border: 1px solid rgba(0,0,0,.125); border-radius: .25rem; }
.card-header { padding: .75rem 1.25rem; margin-bottom: 0; background-color: rgba(0,0,0,.03); border-bottom: 1px solid rgba(0,0,0,.125); }
.mt-3, .my-3 { margin-top: 1rem !important; }
.progress { display: flex; height: 1rem; overflow: hidden; font-size: .75rem; background-color: #e9ecef; border-radius: .25rem; margin: 0; }
.card-body { flex: 1 1 auto; padding: 1.25rem; }
.dashboard-box { display: flex; align-items: center; color: white; border-radius: 5px; margin-bottom: 15px; overflow: hidden; }
.box-icon { width: 80px; display: flex; justify-content: center; align-items: center; font-size: 30px; }
.box-content { background: #fff; color: #333; padding: 15px; flex-grow: 1; }
.box-content h5 { margin: 0; font-weight: bold; }
.box-content small { color: #666; }
.chart-container { height: 400px; margin: auto; }
</style>

<script>
$(".table").dataTable();
const ctx = document.getElementById('medalChart').getContext('2d');

$.get("../list.php?s=medals", function(res) {
    console.log(res);
    if (res.status && Array.isArray(res.data)) {
        const labels = [], goldData = [], silverData = [], bronzeData = [];
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
                    { label: '🥇 Gold', data: goldData, backgroundColor: '#f1c40f', borderRadius: 8, barThickness: 30 },
                    { label: '🥈 Silver', data: silverData, backgroundColor: '#bdc3c7', borderRadius: 8, barThickness: 30 },
                    { label: '🥉 Bronze', data: bronzeData, backgroundColor: '#e67e22', borderRadius: 8, barThickness: 30 }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            pointStyle: 'rectRounded',
                            padding: 20,
                            font: { size: 14, weight: 'bold' }
                        }
                    },
                    tooltip: {
                        backgroundColor: '#333',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: '#fff',
                        borderWidth: 1,
                        padding: 12,
                        cornerRadius: 6,
                        callbacks: {
                            label: function(context) {
                                return `${context.dataset.label}: ${Math.round(context.parsed.y)}`;
                            }
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 13, weight: 'bold' } } },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#eee', borderDash: [5,5] },
                        title: { display: true, text: 'No. of Events Won', font: { size: 14 } },
                        ticks: { stepSize: 1, callback: value => (value % 1 === 0 ? value : '') }
                    }
                }
            }
        });
    } else {
        console.warn("No medal data found or invalid response.");
    }
});
</script>
