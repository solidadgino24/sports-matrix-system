<?php 
    include "../conn.php";
    session_start();
    $_SESSION['game_mode'] = $_GET['id'];
    
?>
<div class="main">
    <div class="head">
        <h2>Game Modes</h2>
        <div>
            <button class='btn btn-secondary btn_bck'>Back</button> 
        </div>
    </div>
    <div class="body">
        <div style="padding:10px;" class="col-md-12">
            <table id="liveDataTableSportsCategory" class="dataTable">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Players</th>
                        <th>Category</th>
                        <th>Scoring</th>
                        <th>Point Base</th>
                        <th>Sets</th>
                        <th>date Added</th>
                        <th>Options</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
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
    padding: 8px;
    margin-top: 10px;
}
.head > div > button {
    border: 1px solid #272c33;
    background-color: coral;
    color: #fff;
    font-weight: bold;
}
tr > td > button{
    padding: 0px 24px;
    border-radius: 5px;
    border: 1px solid #272c33;
    border-bottom: 4px solid #272c33;
}
tr > td > button:hover{
    border: 1px solid rgb(213, 213, 213);
    border-bottom: 4px solid rgb(158, 178, 206);
}
</style>
<script>
    $(".btn_bck").click(function(){
        $.get("sports.php",function(e){
            $(".content-main").remove();
            $(".content").hide();
            $(".content").html(`<div class="content-main">${e}</div>`).fadeIn(200);
        })
    })
    $(".modify_sport_btn").click(function(){
        $.get("modify_sports.php",function(e){
            $(".content-main").remove();
            $(".content").hide();
            $(".content").html(`<div class="content-main">${e}</div>`).fadeIn(200);
        })
    })
</script>