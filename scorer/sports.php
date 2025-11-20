<?php include "../conn.php"?>
<div class="main">
    <div class="head">
        <h2>Sports</h2>
    </div>
    <div class="body">
        <div class="display-flexed">
            <?php
            $sql = $con->query("SELECT * FROM tbl_sports");
            while($row = mysqli_fetch_assoc($sql)){
            ?>
            <div class="sport_holder" title="Game: <?php echo $row['name'] ?>" sport_id="<?php echo $row['sport_id'] ?>">
                <img src="data:image/png;base64,<?php echo $row['img'] ?>" alt="">
                <h4><?php echo $row['name'] ?></h4>
            </div>
            <?php 
                }
            ?>
        </div>
        <div class="cleartfix"></div>
    </div>
</div>
<style>
.head{
    display: flex;
    flex-direction: row;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #272c33;
}
.head > button{
    border: 1px solid #272c33;
    background-color: coral;
    color: #fff;
    font-weight: bold;
}
.display-flexed{
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
    justify-content: space-evenly;
    height: 78vh;
    overflow: auto;
    align-content: flex-start;
}
.display-flexed > div{
    width: 24%;
    border: 1px solid;
    display: flex;
    flex-direction: column;
    align-items: center;
    height: 200px;
    border-radius: 10px;
    margin-top: 10px;
    justify-content: center;
    flex-wrap: wrap;
    padding: 8px;
}
.sport_holder > div{
    display: flex;
    flex-direction: row;
    align-items: center;
    width: 100%;
    justify-content: space-evenly;
    margin-bottom:10px;
}
.sport_holder > img{
    max-width: 50%;
}
.sport_holder{
    cursor: pointer;
}
</style>