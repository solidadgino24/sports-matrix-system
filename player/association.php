<?php include "../conn.php"; 
session_start();
$ev_id = $_SESSION['ev_id'] ?? 0; // Selected event
?>
<div class="main">
    <div class="head">
        <h2>Colleges</h2>
    </div>
    <div class="body">
        <div class="display-flexed">
            <?php
            $sql = $con->query("SELECT * FROM tbl_association ORDER BY name ASC");
            while($row = mysqli_fetch_assoc($sql)){
                $ass_id = $row['ass_id'];
                $logo = !empty($row['img_logo']) ? "data:image/png;base64,".base64_encode($row['img_logo']) : "../assets/default-logo.png";
            ?>
            <div class="association_holder" title="Association: <?= htmlspecialchars($row['name']) ?>" ass_id="<?= $ass_id ?>">
                <div class="assoc-top">
                    <img src="<?= $logo ?>" alt="Logo">
                    <div class="assoc-info">
                        <h3><?= htmlspecialchars($row['name']) ?></h3>
                        <p><?= htmlspecialchars($row['ass_desc']) ?></p>
                    </div>
                </div>
                <h4>Medals</h4>
                <ul>
                    <li class="gold">Gold: <span>0</span></li>
                    <li class="silver">Silver: <span>0</span></li>
                    <li class="bronze">Bronze: <span>0</span></li>
                </ul>
            </div>
            <?php } ?>
        </div>
    </div>
</div>


<style>
/* --- Base Layout --- */
.main {
    padding: 10px;
}
.head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 2px solid #272c33;
    margin-bottom: 10px;
}
.head h2 {
    font-weight: bold;
}
.body {
    padding: 5px;
}

/* --- Associations Grid --- */
.display-flexed {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 15px;
    overflow-y: auto;
    max-height: 78vh;
    align-content: flex-start;
}

/* --- Each Card --- */
.association_holder {
    width: 23%;
    min-width: 250px;
    border: 1px solid #ccc;
    border-radius: 12px;
    padding: 12px;
    background: #fff;
    transition: all 0.2s ease-in-out;
    cursor: pointer;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}
.association_holder:hover {
    transform: scale(1.02);
    box-shadow: 0 4px 10px rgba(0,0,0,0.15);
}

/* --- Card Content --- */
.assoc-top {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 10px;
}
.assoc-top img {
    width: 80px;
    height: 80px;
    object-fit: contain;
    border-radius: 50%;
    border: 1px solid #ddd;
}
.assoc-info h3 {
    font-size: 1.1rem;
    margin: 0;
    color: #222;
}
.assoc-info p {
    font-size: 0.9rem;
    color: #555;
    margin: 5px 0 0;
}

/* --- Medal List --- */
.association_holder h4 {
    margin: 5px 0;
    color: #333;
}
.association_holder ul {
    list-style: none;
    padding-left: 10px;
    margin: 0;
}
.association_holder li {
    font-weight: bold;
    margin-bottom: 4px;
}
.gold { color: goldenrod; }
.silver { color: gray; }
.bronze { color: sienna; }

/* --- Responsive Layout --- */
@media (max-width: 1200px) {
    .association_holder { width: 45%; }
}
@media (max-width: 768px) {
    .association_holder { width: 90%; }
    .assoc-top {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
    .assoc-top img {
        width: 100px;
        height: 100px;
    }
}
@media (max-width: 480px) {
    .head {
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
    }
    .display-flexed {
        gap: 10px;
    }
}
</style>

<script>
$(".association_holder").each(function(){
    let id = $(this).attr("ass_id");
    let element = $(this);
    $.post("../list.php?s=medals_by_assoc", {ass_id:id, ev_id: <?= (int)$ev_id ?>}, function(res){
        if(res.status){
            element.find(".gold span").text(res.data.gold);
            element.find(".silver span").text(res.data.silver);
            element.find(".bronze span").text(res.data.bronze);
        }
    }, 'json');
});
</script>
