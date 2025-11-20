<?php 
include "../conn.php";
session_start();
$_SESSION['game_mode'] = $_GET['id'];
?>
<div class="main">
    <div class="head">
        <h2>Game Modes</h2>
        <div class="head-buttons">
            <button class='btn btn-secondary btn_bck'>Back</button>
        </div>
    </div>

    <div class="body">
        <div class="table-container">
            <table id="liveDataTableSportsCategory" class="dataTable">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Players</th>
                        <th>Category</th>
                        <th>Scoring</th>
                        <th>Point Base</th>
                        <th>Sets</th>
                        <th>Date Added</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Data loaded dynamically -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.main {
    padding: 15px;
    font-family: Arial, sans-serif;
}

/* Header */
.head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 2px solid #272c33;
    padding-bottom: 10px;
    margin-bottom: 20px;
}
.head h2 {
    margin: 0;
    font-size: 24px;
    color: #272c33;
}
.head-buttons button {
    border: none;
    background-color: seagreen;
    color: #fff;
    font-weight: bold;
    padding: 8px 16px;
    border-radius: 6px;
    cursor: pointer;
    transition: background 0.2s ease, transform 0.15s ease;
}
.head-buttons button:hover {
    background-color: mediumseagreen;
    transform: translateY(-2px);
}

/* Table */
.table-container {
    overflow-x: auto;
}
.dataTable {
    width: 100%;
    border-collapse: collapse;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}
.dataTable th, .dataTable td {
    padding: 12px 15px;
    text-align: center;
    border-bottom: 1px solid #ddd;
    font-size: 14px;
}
.dataTable th {
    background-color: #f5f5f5;
    font-weight: bold;
    color: #333;
}
.dataTable tr:hover {
    background-color: #f0f8ff;
}

/* Responsive */
@media(max-width: 768px) {
    .head {
        flex-direction: column;
        align-items: flex-start;
    }
    .head-buttons {
        margin-top: 10px;
        width: 100%;
    }
    .head-buttons button {
        margin: 5px 0 0 0;
        width: 100%;
    }
}
</style>

<script>
$(".btn_bck").click(function(){
    $.get("sports.php", function(e){
        $(".content-main").remove();
        $(".content").hide();
        $(".content").html(`<div class="content-main">${e}</div>`).fadeIn(200);
    });
});
</script>
