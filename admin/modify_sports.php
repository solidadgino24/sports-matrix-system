<?php
include "../conn.php";
session_start();
$id = $_SESSION['game_mode'];
$sql = $con->query("SELECT * FROM tbl_sports WHERE sport_id='$id'");
$row = mysqli_fetch_assoc($sql);
$img = "data:image/png;base64,".$row['img'];
?>
<div class="main">
    <div class="head">
        <h2>Modify Sports</h2>
        <button class='btn btn-secondary btn_bck'>Back</button>
    </div>

    <div class="body">
        <!-- Image preview and rules -->
        <div class="sport_preview">
            <img src="<?php echo $img ?>" alt="Sport Image">
            <button class="btn see_rules">See Rules</button>
        </div>

        <!-- Modify form -->
        <form action="#" id="modify_sport_form" class="sport_form">
            <input type="hidden" id="sport_id" value="<?php echo $id ?>">

            <div class="form-group">
                <label for="name">Sport Name</label>
                <input type="text" id="name" class="form-control" value="<?php echo $row['name'] ?>">
            </div>

            <div class="form-group">
                <label for="img">Change Image</label>
                <input type="file" id="img" class="form-control" accept=".img,.png,.jpeg,.jpg">
            </div>

            <div class="form-group">
                <label for="rules">General Rules (PDF, Images)</label>
                <input type="file" id="rules" class="form-control" accept=".pdf,.png,.jpeg,.jpg">
            </div>

            <div class="form-actions">
                <button class='btn btn-success'>Save</button>
            </div>
        </form>
    </div>
</div>

<style>
.main {
    padding: 20px;
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
    font-size: 22px;
}
.head > button {
    border: none;
    background-color: coral;
    color: #fff;
    font-weight: bold;
    padding: 8px 16px;
    border-radius: 6px;
    cursor: pointer;
    transition: background 0.2s ease, transform 0.15s ease;
}
.head > button:hover {
    background-color: #e06b4d;
    transform: translateY(-2px);
}

/* Body layout */
.body {
    display: flex;
    gap: 30px;
    flex-wrap: wrap;
}

/* Image preview */
.sport_preview {
    flex: 1 1 300px;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 10px;
}
.sport_preview img {
    width: 250px;
    height: auto;
    border: 1px solid #272c33;
    border-radius: 8px;
    margin-bottom: 15px;
    object-fit: contain;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.sport_preview img:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 12px rgba(0,0,0,0.2);
}
.sport_preview .btn {
    width: 100%;
}

/* Form styling */
.sport_form {
    flex: 2 1 400px;
    display: flex;
    flex-direction: column;
    gap: 15px;
    background: #fff;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
.form-group {
    display: flex;
    flex-direction: column;
}
.form-group label {
    font-weight: bold;
    margin-bottom: 6px;
}
.form-group input {
    padding: 8px 12px;
    border-radius: 6px;
    border: 1px solid #ccc;
    font-size: 14px;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}
.form-group input:focus {
    border-color: coral;
    box-shadow: 0 0 5px rgba(255,127,80,0.3);
    outline: none;
}

/* Save button */
.form-actions {
    text-align: right;
}
.form-actions .btn-success {
    background-color: coral;
    color: #fff;
    border: none;
    padding: 10px 20px;
    border-radius: 6px;
    font-weight: bold;
    cursor: pointer;
    transition: background 0.2s ease, transform 0.15s ease;
}
.form-actions .btn-success:hover {
    background-color: #e06b4d;
    transform: translateY(-2px);
}

/* Responsive */
@media (max-width: 768px) {
    .body {
        flex-direction: column;
        align-items: center;
    }
    .sport_preview, .sport_form {
        width: 100%;
    }
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

$(".see_rules").click(function(){
    window.open("../<?php echo $row['rules']?>")
})

// Image preview
$("#img").change(function(){
    var image = this.files[0];
    let pic = $(".sport_preview > img");
    pic.hide();
    var reader = new FileReader();
    reader.onload = function(e){
        pic.fadeIn(100);
        pic.attr("src", e.target.result);
    }
    reader.readAsDataURL(image);
});
</script>
