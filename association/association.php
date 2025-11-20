<?php include "../conn.php"; 
session_start();
$ev_id = $_SESSION['ev_id'] ?? 0; // Selected event
?>
<div class="main">
    <div class="head">
        <h2>Colleges</h2>
        <button class='btn btn-success add_ass_btn'>+ Add College</button>
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
                <section class="action_btn">
                    <span class='btn-success edit_ass' data-id="<?= $ass_id ?>"><i class="glyphicon glyphicon-pencil"></i></span>
                    <span class='btn-danger delete_ass' data-id="<?= $ass_id ?>"><i class="glyphicon glyphicon-trash"></i></span>
                </section>

                <img src="<?= $logo ?>" alt="Logo">
                <h4><?= htmlspecialchars($row['name']) ?></h4>
                <p><?= htmlspecialchars($row['ass_desc']) ?></p>

                <ul>
                    <li class="gold">Gold: <span>0</span></li>
                    <li class="silver">Silver: <span>0</span></li>
                    <li class="bronze">Bronze: <span>0</span></li>
                </ul>
            </div>
            <?php } ?>
        </div>
        <div class="cleartfix"></div>
    </div>
</div>


<style>
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
.head > button:hover {
    background-color: mediumseagreen;
}

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
.action_btn > span {
    width: 28px;
    height: 28px;
    display: flex;
    justify-content: center;
    align-items: center;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
}
.action_btn > .btn-success {
    background-color: seagreen;
    color: #fff;
}
.action_btn > .btn-danger{
    background-color: red;
    color: #fff;
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
