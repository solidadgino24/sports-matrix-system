<?php
include "../conn.php";
session_start();

// Use selected event
$ev_id = $_SESSION['ev_id'] ?? 0;

// Fetch all associations
$sql = $con->query("SELECT * FROM tbl_association ORDER BY name ASC");
?>
<div class="main">
    <div class="head">
        <h2>Colleges</h2>
        <button class='btn btn-success add_ass_btn'>+ Add College</button>
    </div>
    <div class="body">
        <div class="display-flexed">
            <?php
            if($sql && $sql->num_rows > 0){
                while($row = mysqli_fetch_assoc($sql)){
                    $ass_id = $row['ass_id'];
                    $name = htmlspecialchars($row['name']);
                    $desc = htmlspecialchars($row['ass_desc']);

                    $logo = !empty($row['img_logo']) 
                        ? "data:image/png;base64,".base64_encode($row['img_logo']) 
                        : "../assets/default-logo.png";

                    // Only gold, silver, bronze counts
                    $medals = ['gold'=>0,'silver'=>0,'bronze'=>0];

                    if($ev_id){
                        $teams = $con->query("
                            SELECT t.place
                            FROM tbl_team t
                            LEFT JOIN tbl_tournament tn ON t.tourna_id = tn.tourna_id
                            WHERE t.ass_id='$ass_id' AND tn.ev_id='$ev_id' AND t.disqualify=0
                        ");
                        while($t = mysqli_fetch_assoc($teams)){
                            $p = (int)$t['place'];
                            if($p===0) $medals['gold']++;
                            elseif($p===1) $medals['silver']++;
                            elseif($p===2) $medals['bronze']++;
                        }
                    }
            ?>
            <div class="association_holder" data-ass_id="<?= $ass_id ?>" title="Association: <?= $name ?>">
                <section class="action_btn">
                    <span class='btn-success edit_ass' data-id="<?= $ass_id ?>"><i class="glyphicon glyphicon-pencil"></i></span>
                    <span class='btn-danger delete_ass' data-id="<?= $ass_id ?>"><i class="glyphicon glyphicon-trash"></i></span>
                </section>

                <img src="<?= $logo ?>" alt="Logo">
                <h4><?= $name ?></h4>
                <p><?= $desc ?></p>

                <ul>
                    <li class="gold">Gold: <span><?= $medals['gold'] ?></span></li>
                    <li class="silver">Silver: <span><?= $medals['silver'] ?></span></li>
                    <li class="bronze">Bronze: <span><?= $medals['bronze'] ?></span></li>
                </ul>
            </div>
            <?php
                }
            } else {
                echo "<p style='text-align:center; color:#666; width:100%;'>No associations found.</p>";
            }
            ?>
        </div>
        <div class="cleartfix"></div>
    </div>
</div>


<style>
/* Keep your existing styles */
.head{
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #272c33;
    padding: 10px 0;
}
.head > button{
    border: none;
    background-color: seagreen;
    color: #fff;
    font-weight: bold;
    padding: 8px 15px;
    border-radius: 6px;
    cursor: pointer;
    transition: background 0.2s ease;
}
.head > button:hover { background-color: mediumseagreen; }

.display-flexed{
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    height: 78vh;
    overflow-y: auto;
    padding: 15px;
}
.display-flexed > .association_holder{
    flex: 1 1 calc(25% - 20px);
    max-width: calc(25% - 20px);
    min-width: 200px;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 12px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-start;
    padding: 15px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    position: relative;
}
.display-flexed > .association_holder:hover{
    transform: translateY(-5px);
    box-shadow: 0 6px 12px rgba(0,0,0,0.2);
}
.association_holder > img{
    max-width: 80px;
    max-height: 80px;
    object-fit: contain;
    margin-bottom: 12px;
}
.association_holder > h4{
    margin: 5px 0;
    font-size: 16px;
    color: #333;
    text-align: center;
}
.association_holder > p{
    font-size: 14px;
    color: #555;
    text-align: center;
    margin-bottom: 10px;
}
.association_holder > ul{
    list-style-type: none;
    padding: 0;
    margin: 0;
    display: flex;
    justify-content: space-around;
    width: 100%;
}
.association_holder > ul > li{
    font-weight: bold;
    color: #272c33;
}

/* Floating small buttons */
.action_btn{
    display: flex;
    gap: 5px;
    position: absolute;
    top: 8px;
    right: 8px;
}
.action_btn > span{
    width: 28px;
    height: 28px;
    display: flex;
    justify-content: center;
    align-items: center;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
}
.btn-success { background-color: seagreen; color: #fff; }
.btn-danger { background-color: red; color: #fff; }
</style>

<script>
$(document).ready(function(){

    // Open focus_association.php on click
    $(document).on("click", ".association_holder", function(e){
        if($(e.target).closest(".action_btn").length) return;
        const ass_id = $(this).data("ass_id");
        $.get(`focus_association.php?ass_id=${ass_id}`, function(html){
            $(".content-main").remove();
            $(".content").hide();
            $(".content").html(`<div class="content-main">${html}</div>`).fadeIn(200);
        });
    });

    // Edit association
    $(document).on("click", ".edit_ass", function(e){
        e.stopPropagation();
        const ass_id = $(this).data("id");
        $.get(`modify_association.php?id=${ass_id}`, function(html){
            $(".content-main").remove();
            $(".content").hide();
            $(".content").html(`<div class="content-main">${html}</div>`).fadeIn(200);
        });
    });

    // Delete association
    $(document).on("click", ".delete_ass", function(e){
        e.stopPropagation();
        const ass_id = $(this).data("id");
        if(!confirm("Are you sure you want to delete this association?")) return;

        $.post("delete_association.php", {ass_id: ass_id}, function(res){
            if(res && res.success){
                $(`.association_holder[data-ass_id='${ass_id}']`).fadeOut(300, function(){ $(this).remove(); });
            } else {
                alert((res && res.message) ? res.message : "Failed to delete association.");
            }
        }, "json");
    });

});
</script>
