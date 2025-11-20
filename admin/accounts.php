<?php include "../conn.php"?>
<div class="main">
  <div class="head">
    <h2>Accounts</h2>
  </div>

  <div class="body row gy-3">
    <!-- ✅ Accounts Table -->
    <div class="col-md-7 col-12 table_body">
      <div class="card">
        <div class="card-header">
          <h4>Active Accounts</h4>
        </div>
        <div class="card-body table-responsive">
          <table class="table table-striped table-hover align-middle">
            <thead>
              <tr>
                <th>Username</th>
                <th>Fullname</th>
                <th>User Type</th>
                <th>Association</th>
              </tr>
            </thead>
            <tbody>
              <?php 
                $sql = $con->query("SELECT u.user_id,u.username,p.fullname,ut.type,s.name 
                                    FROM tbl_user AS u 
                                    LEFT JOIN tbl_profile_ass AS p ON u.user_id=p.user_id 
                                    LEFT JOIN tbl_user_type AS ut ON u.user_type=ut.type_id 
                                    LEFT JOIN tbl_association_staff AS ast ON u.user_id=ast.user_id 
                                    LEFT JOIN tbl_association AS s ON ast.ass_id=s.ass_id 
                                    WHERE u.status = '1' AND u.user_type !='3' AND u.user_type !='1'");
                while($row=mysqli_fetch_assoc($sql)){
              ?>
              <tr row_id='<?php echo $row['user_id'] ?>'>
                <td data-label="Username"><?php echo $row['username'] ?></td>
                <td data-label="Fullname"><?php echo $row['fullname'] ?></td>
                <td data-label="User Type"><?php echo $row['type'] ?></td>
                <td data-label="Association"><?php echo $row['name'] ?></td>
              </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ✅ Verifications Table -->
    <div class="col-md-5 col-12 verify">
      <div class="card">
        <div class="card-header">
          <h4>Verifications</h4>
        </div>
        <div class="card-body table-responsive">
          <?php 
            $sql = $con->query("SELECT * FROM tbl_profile_ass AS p 
                                LEFT JOIN tbl_user AS u ON p.user_id=u.user_id 
                                WHERE u.status = '0' AND u.user_type !='3'");
            if(mysqli_num_rows($sql) > 0){
          ?>
          <table class="table table-sm table-hover verification-table align-middle">
            <thead>
              <tr>
                <th>Name</th>
                <th class="text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php while($row=mysqli_fetch_assoc($sql)){ ?>
              <tr>
                <td data-label="Name"><?= $row['fullname'] ?></td>
                <td data-label="Actions" class="text-center">
                  <div class="btn-group d-flex flex-wrap justify-content-center">
                    <button class='btn btn-sm btn-primary review_request' data-id="<?= $row['user_id'] ?>">Review</button>
                    <button class='btn btn-sm btn-success verify_request' data-id="<?= $row['user_id'] ?>">Verify</button>
                    <button class='btn btn-sm btn-danger Reject_request' data-id="<?= $row['user_id'] ?>">Reject</button>
                  </div>
                </td>
              </tr>
              <?php } ?>
            </tbody>
          </table>
          <?php 
            } else {
                echo "<p class='no_v text-muted'>No verifications.</p>";
            }
          ?>
        </div>
      </div>
    </div>
  </div>

  <div class="clearfix"></div>
</div>

<style>
.main {
  padding: 15px;
}
.head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 2px solid #f0f0f0;
  margin-bottom: 15px;
}
.head h2 {
  font-size: 22px;
  font-weight: 600;
  color: #333;
  margin: 0;
}

/* ✅ Card Layout */
.card {
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  background: #fff;
  box-shadow: 0 2px 6px rgba(0,0,0,0.05);
  overflow: hidden;
  margin-bottom: 15px;
}
.card-header {
  padding: 10px 15px;
  background: #f9f9f9;
  border-bottom: 1px solid #e0e0e0;
}
.card-header h4 {
  margin: 0;
  font-size: 16px;
  font-weight: 600;
  color: #333;
}
.card-body {
  padding: 15px;
}

/* ✅ Tables */
.table {
  width: 100%;
  font-size: 14px;
}
.table th {
  background: #f5f5f5;
  font-weight: 600;
  color: #555;
  text-align: center;
}
.table td {
  vertical-align: middle;
  text-align: center;
}
.table tbody tr:hover {
  background: #fafafa;
}

/* ✅ Verifications Table */
.verification-table td .btn {
  margin: 3px;
  min-width: 70px;
}
.no_v {
  margin: 10px 0;
  font-style: italic;
  text-align: center;
}

/* ✅ Responsive Adjustments */
@media (max-width: 768px) {
  .head {
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
  }

  .table thead {
    display: none;
  }

  .table tbody tr {
    display: block;
    background: #fff;
    margin-bottom: 12px;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    padding: 10px;
  }

  .table tbody td {
    display: flex;
    justify-content: space-between;
    text-align: left;
    padding: 8px 6px;
    border: none;
  }

  .table tbody td::before {
    content: attr(data-label);
    font-weight: 600;
    color: #444;
  }

  .btn-group {
    flex-direction: column;
  }

  .btn-group .btn {
    width: 100%;
    margin: 4px 0;
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
