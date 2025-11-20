<?php
include "../conn.php";
session_start();

header('Content-Type: text/html; charset=utf-8');

if (!isset($_GET['ass_id'])) {
    echo "<script>alert('No college selected.'); window.location='association.php';</script>";
    exit;
}

$ass_id = intval($_GET['ass_id']);
$sql = $con->query("SELECT * FROM tbl_association WHERE ass_id = '$ass_id'");
$assoc = mysqli_fetch_assoc($sql);

if (!$assoc) {
    echo "<script>alert('College not found.'); window.location='association.php';</script>";
    exit;
}

// 🧩 Handle AJAX submission
// 🧩 Handle AJAX submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_program'])) {
    header('Content-Type: application/json');
    $ass_id = intval($_POST['ass_id'] ?? 0);
    $prog_name = trim($_POST['prog_name'] ?? '');
    $response = ['success' => false, 'message' => ''];

    if ($ass_id <= 0 || empty($prog_name)) {
        $response['message'] = 'Missing program name or association ID';
        echo json_encode($response);
        exit;
    }

    // 🖼️ Handle image upload
    $img_data = null;
    if (!empty($_FILES['prog_logo']['tmp_name']) && is_uploaded_file($_FILES['prog_logo']['tmp_name'])) {
        $img = file_get_contents($_FILES['prog_logo']['tmp_name']);
        if ($img === false) {
            $response['message'] = 'Failed to read uploaded file.';
            echo json_encode($response);
            exit;
        }
        $img_data = base64_encode($img);
    }

    // 🧩 Insert data safely
    if ($img_data) {
        $stmt = $con->prepare("INSERT INTO tbl_programs (ass_id, prog_name, img_logo, date_created) VALUES (?, ?, ?, NOW())");
        if (!$stmt) {
            $response['message'] = 'Prepare failed: ' . $con->error;
            echo json_encode($response);
            exit;
        }
        $stmt->bind_param("iss", $ass_id, $prog_name, $img_data);
    } else {
        $stmt = $con->prepare("INSERT INTO tbl_programs (ass_id, prog_name, date_created) VALUES (?, ?, NOW())");
        if (!$stmt) {
            $response['message'] = 'Prepare failed: ' . $con->error;
            echo json_encode($response);
            exit;
        }
        $stmt->bind_param("is", $ass_id, $prog_name);
    }

    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Program added successfully!';
    } else {
        $response['message'] = 'Execute failed: ' . $stmt->error;
    }

    echo json_encode($response);
    exit;
}

?>

<div class="main">
    <div class="head">
        <h2>Add Program — <?php echo htmlspecialchars($assoc['name']); ?></h2>
        <a href="focus_association.php?ass_id=<?php echo $ass_id; ?>" class="btn btn-secondary">Back</a>
    </div>

    <div class="body">
        <div class="form_card">
            <form method="POST" enctype="multipart/form-data" id="addProgramForm">
                <input type="hidden" name="ass_id" value="<?php echo $ass_id; ?>">

                <div class="form-group">
                    <label for="prog_name">Program Name:</label>
                    <input type="text" name="prog_name" id="prog_name" class="form-control" placeholder="e.g. BSIT, BSIS, BTVTEd..." required>
                </div>

                <div class="form-group image-upload">
                    <div class="upload-section">
                        <label for="prog_logo">Program Logo (optional):</label>
                        <input type="file" name="prog_logo" id="prog_logo" class="form-control" accept="image/*">
                        <small class="text-muted">Upload a square image (e.g. 300×300px)</small>
                    </div>
                    <div class="preview-box">
                        <img id="imgPreview" src="../icons/ico.png" alt="Preview">
                    </div>
                </div>

                <button type="submit" name="add_program" class="btn btn-primary">Add Program</button>
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

    // 🖼️ Preview uploaded image
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

    // 🧭 Dynamic Back button
    $(document).on("click", ".btn-secondary[href^='focus_association.php']", function(e){
        e.preventDefault();
        const url = $(this).attr("href");
        $.get(url, function(res){
            $(".content-main").remove();
            $(".content").hide();
            $(".content").html(`<div class="content-main">${res}</div>`).fadeIn(200);
        });
    });

    // Submit form via AJAX
    $("#addProgramForm").on("submit", function(e){
        e.preventDefault();
        const formData = new FormData(this);
        formData.append("add_program", true);

        $.ajax({
            url: "add_program_action.php",
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function(res){
                if(res.success){
                    alert(res.message);
                    const ass_id = $("input[name='ass_id']").val();
                    $.get(`focus_association.php?ass_id=${ass_id}`, function(html){
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
