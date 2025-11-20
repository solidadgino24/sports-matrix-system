<?php
include_once("../conn.php");
session_start(); 

$ev_id = $_SESSION['ev_id'];
$sql = $con->query("SELECT t.maximum, t.minimum, t.tourna_id, t.status,
                           s.img, s.name AS sport_name, gm.name, c.category, gm.players 
                    FROM tbl_tournament AS t 
                    LEFT JOIN tbl_game_modes AS gm ON t.game_id = gm.game_id
                    LEFT JOIN tbl_sports AS s ON gm.sport_id = s.sport_id
                    LEFT JOIN tbl_game_mode_cat AS c ON gm.gm_cat_id = c.gm_cat_id
                    WHERE t.ev_id = '$ev_id'");
?>
<div class="main">
  <div class="head">
    <h2>Tournament</h2>
    <button class='btn btn-secondary btn_add_tourna'>Add Tournament</button>
  </div>

  <div class="body">
    <div class="table-responsive">
      <table class="table table-striped table-hover align-middle">
        <thead>
          <tr>
            <th>Logo</th>
            <th>Sport</th>
            <th>Game Mode</th>
            <th>Category</th>
            <th>Players</th>
            <th>Max / Min</th>
            <!-- <th>Type</th> -->
            <th>Status</th>
            <th>Options</th>
          </tr>
        </thead>
        <tbody>
          <?php while($row = mysqli_fetch_assoc($sql)){ ?>
          <tr>
            <td data-label="Logo"><img src="data:image/png;base64,<?php echo $row['img'] ?>" alt="logo"></td>
            <td data-label="Sport"><?php echo $row['sport_name'] ?></td>
            <td data-label="Game Mode"><?php echo $row['name'] ?></td>
            <td data-label="Category"><?php echo $row['category'] ?></td>
            <td data-label="Players"><?php echo $row['players'] ?> Player(s)</td>
            <td data-label="Max / Min"><?php echo $row['maximum']." / ".$row['minimum'] ?></td>
            <td data-label="Status">
              <?php 
                $status = intval($row['status']); // ensure integer
                if ($status === 0) {
                  echo "<span class='badge' style='background-color: #ffc107; color: #000;'>Not Started</span>";
                } elseif ($status === 1) {
                  echo "<span class='badge' style='background-color: #28a745; color: #fff;'>Ongoing</span>";
                } elseif ($status === 2) {
                  echo "<span class='badge' style='background-color: #dc3545; color: #fff;'>Ended</span>";
                } else {
                  echo "<span class='badge' style='background-color: #6c757d; color: #fff;'>Unknown</span>";
                }
              ?>
            </td>

            <td data_id="<?php echo $row['tourna_id'] ?>" data-label="Options">
              <button class="btn_modify_tourna">Edit & Remove</button>
              <button class="btn_view_tourna">View</button>
            </td>
          </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </div>
  <div class="clearfix"></div>
</div>

<style>
.main {
  background: #fff;
  padding: 15px;
  border-radius: 8px;
  box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}
.head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-bottom: 8px;
  margin-bottom: 15px;
  border-bottom: 2px solid #eee;
}
.head > h2 {
  margin: 0;
  font-size: 20px;
  font-weight: bold;
  color: #333;
}
.head > button {
  background: seagreen;
  border: none;
  padding: 8px 15px;
  border-radius: 6px;
  font-weight: bold;
  color: #fff;
  transition: 0.3s;
}
.head > button:hover {
  background: mediumseagreen;
}
.table {
  width: 100%;
  border-collapse: collapse;
  font-size: 14px;
}
.table thead {
  background: #f8f9fa;
}
.table thead th {
  padding: 10px;
  text-align: center;
  font-weight: bold;
  color: #555;
  border-bottom: 2px solid #ddd;
}
.table tbody td {
  padding: 10px;
  text-align: center;
  vertical-align: middle;
  word-wrap: break-word;
  white-space: normal;
}
.table tbody tr:nth-child(even) {
  background: #fdfdfd;
}
.table tbody tr:hover {
  background: #f1f7ff;
}
td > img {
  max-height: 50px;
  width: auto;
  border-radius: 4px;
  box-shadow: 0 1px 4px rgba(0,0,0,0.1);
}
tr > td > button {
  padding: 5px 12px;
  border: 1px solid #272c33;
  border-bottom: 3px solid #272c33;
  border-radius: 6px;
  margin: 3px 0;
  width: 110px;
  font-size: 13px;
  cursor: pointer;
  background: #fff;
  transition: all 0.2s ease-in-out;
}
tr > td > button:hover {
  color: #444;
  border-bottom: 1px solid rgb(66, 94, 133);
  transform: translateY(1px);
}
.badge {
  padding: 5px 8px;
  border-radius: 4px;
  font-size: 12px;
}

/* ✅ Mobile Responsiveness */
@media (max-width: 768px) {
  .head {
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
  }

  .head h2 {
    font-size: 18px;
  }

  .head > button {
    align-self: flex-end;
    padding: 6px 12px;
    font-size: 13px;
  }

  .table thead {
    display: none;
  }

  .table tbody tr {
    display: block;
    margin-bottom: 15px;
    background: #fff;
    box-shadow: 0 2px 6px rgba(0,0,0,0.05);
    border-radius: 6px;
    padding: 10px;
  }

  .table tbody td {
    display: flex;
    justify-content: space-between;
    padding: 8px 5px;
    text-align: left;
    border: none;
  }

  .table tbody td::before {
    content: attr(data-label);
    font-weight: bold;
    color: #444;
    flex: 1;
    text-align: left;
  }

  td > img {
    max-height: 40px;
  }

  tr > td > button {
    width: 100%;
  }
}
</style>

<script>
$(".table").dataTable({
  responsive: true,
  paging: true,
  searching: true
});
</script>