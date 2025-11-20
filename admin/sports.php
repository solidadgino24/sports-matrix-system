<?php include "../conn.php"?>
<div class="main">
    <div class="head">
        <h2>Sports</h2>
        <button class='btn btn-secondary add_sport_btn'>Add sports</button>
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
                <button class="btn btn-danger delete_sport_btn" data-id="<?php echo $row['sport_id'] ?>">
                    <i class="fa fa-trash"></i> Delete
                </button>
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
.delete_sport_btn {
    margin-top: 10px;
    background-color: #dc3545;
    border: none;
    color: #fff;
    padding: 6px 12px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    transition: background-color 0.2s ease;
}
.delete_sport_btn:hover {
    background-color: #b02a37;
}

</style>

<script>
$(document).off('click', '.delete_sport_btn').on('click', '.delete_sport_btn', function(e) {
    e.stopPropagation(); // 🧱 Prevent triggering parent click (redirect)
    e.preventDefault(); // 🛑 Stop default behavior if inside link/form

    const id = $(this).data('id');
    const card = $(this).closest('.sport_holder');

    if (confirm('Are you sure you want to delete this sport?')) {
        $.ajax({
            url: 'delete_sport.php',
            method: 'POST',
            data: { sport_id: id },
            cache: false,
            success: function(response) {
                try {
                    const res = JSON.parse(response);
                    if (res.status === 'success') {
                        alert('Sport deleted successfully!');
                        card.fadeOut(300, function() { $(this).remove(); });
                    } else {
                        alert('Error: ' + res.message);
                    }
                } catch (e) {
                    console.error('Invalid JSON:', response);
                    alert('Unexpected error occurred.');
                }
            },
            error: function() {
                alert('Failed to connect to the server.');
            }
        });
    }
});
</script>
