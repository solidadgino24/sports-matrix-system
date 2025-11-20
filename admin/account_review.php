<?php 
include "../conn.php"; 
$id = $_GET['acc_id']; 

$sql = $con->query("SELECT p.*,u.status,ut.type 
    FROM tbl_profile_ass AS p 
    LEFT JOIN tbl_user AS u ON p.user_id=u.user_id 
    LEFT JOIN tbl_user_type AS ut ON u.user_type=ut.type_id 
    WHERE u.user_id = '$id'");
extract(mysqli_fetch_assoc($sql));  

$img = "data:image/png;base64,".$profile; 

$sql = $con->query("SELECT a.name 
    FROM tbl_association_staff AS ast 
    LEFT JOIN tbl_association AS a ON ast.ass_id=a.ass_id 
    WHERE ast.user_id = '$id'");
$associate = mysqli_fetch_assoc($sql);  

if($gender == 1){     
    $gender = "Male"; 
}else{     
    $gender = "Female"; 
} 
?> 

<div class="main">
    <div class="head">
        <h2>Review Account</h2>
        <div class="btns">
            <button class='btn btn-secondary back_to_account'>Back</button>
            <button class='btn btn-success verify_this_account'>Verify</button>
        </div>
    </div>

    <div class="body row">
        <div class="col-md-4">
            <div class="preview">
                <img src="<?php echo $img ?>" alt="Profile">
            </div>
        </div>

        <div class="col-md-8">
            <div class="card-info">
                <div class="form-group">
                    <label>Fullname</label>
                    <p class='form-control static'><?php echo $fullname ?></p>
                </div>
                <div class="form-group">
                    <label>User Type</label>
                    <p class='form-control static'><?php echo $type ?></p>
                </div>
                <div class="form-group">
                    <label>Association</label>
                    <p class='form-control static'><?php echo (isset($associate['name']))? $associate['name'] : ""?></p>
                </div>
                <div class="form-group">
                    <label>Gender</label>
                    <p class='form-control static'><?php echo $gender ?></p>
                </div>
                <div class="form-group">
                    <label>Birthdate</label>
                    <p class='form-control static'><?php echo date("F j, Y",strtotime($birthday)) ?></p>
                </div>
                <div class="form-group">
                    <label>Contact / Email</label>
                    <p class='form-control static'><?php echo $contact." / ".$email ?></p>
                </div>
            </div>
        </div>
    </div>
    <div class="clearfix"></div>
</div>

<style>
.main {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
.head{
    display: flex;
    flex-direction: row;
    justify-content: space-between;
    align-items: center;
    border-bottom: 2px solid #f0f0f0;
    padding-bottom: 10px;
    margin-bottom: 20px;
}
.head h2{
    margin: 0;
    font-size: 20px;
    font-weight: 600;
    color: #333;
}
.btns .btn{
    margin-left: 8px;
}
.preview{
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    height: 220px;
    background: #fafafa;
    display: flex;
    justify-content: center;
    align-items: center;
    overflow: hidden;
}
.preview > img{
    max-width: 100%;
    max-height: 100%;
    object-fit: cover;
    border-radius: 8px;
}
.card-info{
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px 20px;
}
.form-group{
    display: flex;
    flex-direction: column;
}
.form-group label{
    font-weight: 600;
    font-size: 14px;
    margin-bottom: 4px;
    color: #555;
}
.form-control.static{
    background: #f9f9f9;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    padding: 8px 12px;
    font-size: 14px;
    color: #333;
    margin: 0;
}
</style>
