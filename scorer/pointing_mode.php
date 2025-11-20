<?php
$id = $_GET['id'];
include "../conn.php";
session_start();
$sql = $con->query("SELECT * FROM tbl_point_system WHERE game_id='$id' ORDER BY point_id DESC");
?>
<div class="main container mt-3">
    <div class="head mb-3 p-2">
        <button class="btn sport_holder" sport_id="<?php echo $_SESSION['game_mode'] ?>">
            Back
        </button>
        <h2 class="mb-0">Game Mode Point System</h2>
    </div>

    <div class="row body">
        <div class="col-md-5">
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-primary text-white text-header">
                    Add
                </div>
                <div class="card-body">
                    <form action="#" id="point_system_form">
                        <input type="hidden" name="point_id" class="point_id" value = "0">
                        <input type="hidden" name="game_id" value="<?= $id ?>">
                        <div class="form-group mb-3">
                            <label class="fw-bold">Points</label>
                            <input type="number" class="form-control points" name="points" required placeholder="Enter points">
                        </div>
                        <button type="submit" class="btn btn-success w-100 pull-right">
                            Save
                        </button>

                        <button type="button" class="btn btn-danger w-100 pull-right clear" style="display:none">
                            Clear
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    Points List
                </div>
                <div class="card-body">
                    <table class="table table-hover table-striped table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>Points</th>
                                <th>Options</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($sql)) {?>
                            <tr>
                                <td><?= $row['point'] ?></td>
                                <td>
                                    <button class="btn btn-sm btn-warning edit" data='<?= $row['point_id'] ?>'>✏ Edit</button>
                                    <button class="btn btn-sm btn-danger delete_points" data='<?= $row['point_id'] ?>'>🗑 Delete</button>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card-header{
    text-align:center;
    padding:5px;
    border-radius:3px;
    font-weight:bold;
}
.head {
    display: flex;
    flex-direction: row;
    justify-content: space-between;
    align-items: center;
    border-bottom: 2px solid #dee2e6;
    padding-bottom: 5px;
}
.head > button {
    border: none;
    background-color: coral;
    color: #fff;
    font-weight: bold;
    padding: 6px 15px;
    border-radius: 6px;
    transition: 0.3s;
}
#point_system_form button{
    margin: 0 5px;
}
</style>

<script>
$(".table").dataTable({
    paging: true,
    searching: true,
    info: false,
    lengthChange: false
});
$(".edit").click(function(){
    $.post("../list.php?s=points",{id:$(this).attr("data")},function(res){
        if(res.status){
            let data = res.data;
            $(".text-header").text("Editing");
            $(".point_id").val(data.point_id)
            $(".points").val(data.point)
            $(".clear").show(50)
        }
    })
})
$(".clear").click(function(){
    $(".point_id").val(0)
    $(".points").val("")
    $(".clear").hide(50)
    $(".text-header").text("Add");
})
</script>
