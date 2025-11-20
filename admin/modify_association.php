<?php
    include "../conn.php";
    session_start();
    $id = $_GET['id'];
    $sql = $con->query("SELECT * FROM tbl_association WHERE ass_id='$id'");
    $row = mysqli_fetch_assoc($sql);
    $img = "data:image/png;base64,".$row['img_logo'];
?>
<div class="main">
    <div class="head">
        <h2>Modify Association</h2>
    </div>
    <div class="body">
        <div class="col-md-2 col-md-offset-3 preview">
            <img src="<?= $img?>" alt="">
        </div>
        <form action="#" id="mod_ass_form" class="col-md-4">
            <h3>Association</h3>
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" name="name"  id="name" value="<?= $row['name'] ?>" class="form-control formrequire">
                <input type="hidden" name="name"  id="id" value="<?= $id ?>" class="form-control formrequire">
            </div>
            <div class="form-group">
                <label for="desc">Description</label>
                <textarea name="desc" id="desc" class="form-control formrequire"><?= $row['ass_desc'] ?></textarea>
            </div>
            <div class="form-group">
                <label for="logo">Logo</label>
                <input type="file" name="logo" id="logo" accept=".png,.jpeg,.jpg" class="form-control">
            </div>
            <button class="btn btn-success pull-right"> <span class="glyphicon glyphicon-save"></span> Save</button>
        </form>
    </div>
    <div class="clearfix"></div>
</div>
<style>
.head{
    display: flex;
    flex-direction: row;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #272c33;
}
.preview{
    border: 1px solid #272c33;
    padding: 10px;
    margin-top: 20px;
    border-radius: 5px;
    display: flex;
    height: 200px;
    align-items: center;
    justify-content: center;
}
.preview > img{
    height: 90%;
    max-width: 100%;
}
</style>