<?php
include "../conn.php";
session_start();

// --- Handle GET: display the edit form ---
if (!isset($_GET['prog_id'])) {
    echo "<script>alert('No program selected.'); window.location='association.php';</script>";
    exit;
}

$prog_id = intval($_GET['prog_id']);
$sql = $con->query("SELECT * FROM tbl_programs WHERE prog_id = '$prog_id'");
$prog = mysqli_fetch_assoc($sql);

if (!$prog) {
    echo "<script>alert('Program not found.'); window.location='association.php';</script>";
    exit;
}

// 🧩 Check AJAX
$is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

// If AJAX, output only the form container
if ($is_ajax) {
    ?>
    <div class="main">
        <div class="head">
            <h2>Edit Program — <?php echo htmlspecialchars($prog['prog_name']); ?></h2>
            <a href="focus_association.php?ass_id=<?php echo $prog['ass_id']; ?>" class="btn btn-secondary">Back</a>
        </div>

        <div class="body">
            <div class="form_card">
                <form method="POST" enctype="multipart/form-data" id="editProgramForm">
                    <input type="hidden" name="prog_id" value="<?php echo $prog['prog_id']; ?>">

                    <div class="form-group">
                        <label for="prog_name">Program Name:</label>
                        <input type="text" name="prog_name" id="prog_name" class="form-control"
                               value="<?php echo htmlspecialchars($prog['prog_name']); ?>" required>
                    </div>

                    <div class="form-group image-upload">
                        <div class="upload-section">
                            <label for="prog_logo">Program Logo (optional):</label>
                            <input type="file" name="prog_logo" id="prog_logo" class="form-control" accept="image/*">
                            <small class="text-muted">Upload a square image (e.g. 300×300px)</small>
                        </div>
                        <div class="preview-box">
                            <img id="imgPreview" src="<?php
                                echo !empty($prog['img_logo']) 
                                     ? 'data:image/png;base64,'.$prog['img_logo'] 
                                     : '../icons/ico.png'; 
                            ?>" alt="Preview">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
        </div>
    </div>

    <style>
    .main { background: #f9f9f9; padding: 20px; }
    .head { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #ddd; padding-bottom: 10px; margin-bottom: 20px; }
    .head h2 { color: #222; font-weight: 700; }
    .btn-primary, .btn-secondary { border: none; border-radius: 6px; padding: 8px 15px; font-weight: 600; text-decoration: none; cursor: pointer; }
    .btn-primary { background-color: seagreen; color: #fff; }
    .btn-primary:hover { background-color: mediumseagreen; }
    .btn-secondary { background-color: gray; color: #fff; }
    .btn-secondary:hover { background-color: dimgray; }
    .body { display: flex; justify-content: center; align-items: center; height: 70vh; }
    .form_card { background: #fff; border: 1px solid #ddd; border-radius: 12px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); padding: 30px; width: 100%; max-width: 500px; }
    .form-group { margin-bottom: 15px; }
    .form-group label { font-weight: 600; }
    .form-control { width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #ccc; font-size: 15px; }
    .text-muted { font-size: 13px; color: #666; }
    .image-upload { display: flex; align-items: center; justify-content: space-between; gap: 15px; }
    .preview-box { width: 120px; height: 120px; border: 1px solid #ccc; border-radius: 10px; overflow: hidden; display: flex; align-items: center; justify-content: center; background: #f5f5f5; }
    .preview-box img { width: 100%; height: 100%; object-fit: cover; }
    </style>

    <script>
    $(document).ready(function(){
        $("#prog_logo").on("change", function(){
            const file = this.files[0];
            if(file){
                const reader = new FileReader();
                reader.onload = function(e){
                    $("#imgPreview").attr("src", e.target.result);
                };
                reader.readAsDataURL(file);
            }
        });

        $(document).on("click", ".btn-secondary[href^='focus_association.php']", function(e){
            e.preventDefault();
            const url = $(this).attr("href");
            $.get(url, function(res){
                $(".content-main").remove();
                $(".content").hide();
                $(".content").html(`<div class="content-main">${res}</div>`).fadeIn(200);
            });
        });

        $("#editProgramForm").on("submit", function(e){
            e.preventDefault();
            const formData = new FormData(this);
            $.ajax({
                url: "edit_program_action.php",
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                dataType: "json",
                success: function(res){
                    if(res.success){
                        alert(res.message);
                        $.get(`focus_association.php?ass_id=<?php echo $prog['ass_id']; ?>`, function(html){
                            $(".content-main").remove();
                            $(".content").hide();
                            $(".content").html(`<div class="content-main">${html}</div>`).fadeIn(200);
                        });
                    } else {
                        alert(res.message);
                    }
                },
                error: function(xhr){
                    console.log("RAW RESPONSE:", xhr.responseText);
                    alert("An unexpected error occurred.");
                }
            });
        });
    });
    </script>
    <?php
    exit; // stop further rendering for AJAX
} // <-- Close the if ($is_ajax) block properly
?>
