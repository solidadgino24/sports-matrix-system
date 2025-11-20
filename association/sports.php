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
    justify-content: flex-start;
    gap: 20px;
    height: 78vh;
    overflow-y: auto;
    padding: 15px;
}

.display-flexed > .sport_holder{
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
    justify-content: center;
    padding: 15px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    cursor: pointer;
}
.display-flexed > .sport_holder:hover{
    transform: translateY(-5px);
    box-shadow: 0 6px 12px rgba(0,0,0,0.2);
}

.sport_holder > img{
    max-width: 80px;
    max-height: 80px;
    object-fit: contain;
    margin-bottom: 12px;
}
.sport_holder > h4{
    margin: 0;
    font-size: 16px;
    color: #333;
    text-align: center;
}

</style>