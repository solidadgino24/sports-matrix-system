<?php
include "../conn.php";
session_start();

// Detect AJAX
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if (!isset($_GET['ass_id'])) {
    if ($isAjax) {
        echo "<p class='no_data'>Missing college ID.</p>";
    } else {
        echo "<script>alert('Missing college ID.'); window.location='association.php';</script>";
    }
    exit;
}

$ass_id = intval($_GET['ass_id']);
$sql = $con->query("SELECT * FROM tbl_association WHERE ass_id = '$ass_id'");
$assoc = mysqli_fetch_assoc($sql);

if (!$assoc) {
    if ($isAjax) {
        echo "<p class='no_data'>College not found.</p>";
    } else {
        echo "<script>alert('College not found.'); window.location='association.php';</script>";
    }
    exit;
}
?>
<div class="main">
    <div class="head">
        <h2><?php echo htmlspecialchars($assoc['name']); ?> — Programs</h2>
        <div class="buttons">
            <a href="add_program.php?ass_id=<?php echo $ass_id; ?>" class="btn btn-primary">Add Program</a>
            <a href="association.php" class="btn btn-secondary">Back to Colleges</a>
        </div>
    </div>

    <div class="body">
        <div class="display-flexed">
            <?php
            $prog_sql = $con->query("SELECT * FROM tbl_programs WHERE ass_id = '$ass_id' ORDER BY prog_name ASC");
            if ($prog_sql->num_rows > 0) {
                while ($prog = mysqli_fetch_assoc($prog_sql)) {
                    $prog_id = $prog['prog_id'];
                    $prog_name = htmlspecialchars($prog['prog_name']);
                    $img_src = (!empty($prog['img_logo'])) 
                        ? 'data:image/*;base64,' . $prog['img_logo'] 
                        : '../assets/program_icon.png';
            ?>
                <div class="association_holder" data-prog_id="<?php echo $prog_id; ?>" title="Program: <?php echo $prog_name; ?>">
                    <section class="action_btn">
                        <button class='btn btn-success edit_prog' data-id="<?php echo $prog_id; ?>">
                            <i class="glyphicon glyphicon-pencil"></i>
                        </button>
                        <button class='btn btn-danger delete_prog' data-id="<?php echo $prog_id; ?>">
                            <i class="glyphicon glyphicon-trash"></i>
                        </button>
                    </section>
                    <img src="<?php echo $img_src; ?>" alt="Program Icon">
                    <h4><?php echo $prog_name; ?></h4>
                    <p>Created on: <?php echo date('F d, Y', strtotime($prog['date_created'])); ?></p>
                </div>
            <?php
                }
            } else {
                echo "<p class='no_data'>No programs added yet for this college.</p>";
            }
            ?>
        </div>
    </div>
</div>

<style>
/* Same styling as before */
.main { padding: 20px; background: #f9f9f9; }
.head { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #ddd; padding-bottom: 10px; }
.head h2 { color: #222; font-weight: 700; }
.head .buttons { display: flex; gap: 10px; }
.btn-primary, .btn-secondary { border: none; border-radius: 6px; padding: 8px 15px; font-weight: 600; text-decoration: none; transition: 0.2s ease; }
.btn-primary { background-color: seagreen; color: #fff; }
.btn-primary:hover { background-color: mediumseagreen; }
.btn-secondary { background-color: gray; color: #fff; }
.btn-secondary:hover { background-color: dimgray; }

.display-flexed { display: flex; flex-wrap: wrap; gap: 20px; margin-top: 20px; overflow-y: auto; padding: 10px; }
.association_holder { flex: 1 1 calc(25% - 20px); max-width: calc(25% - 20px); min-width: 220px; background: #fff; border: 1px solid #ddd; border-radius: 12px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); display: flex; flex-direction: column; align-items: center; padding: 15px; position: relative; transition: transform 0.2s ease, box-shadow 0.2s ease; cursor: pointer; }
.association_holder:hover { transform: translateY(-5px); box-shadow: 0 6px 12px rgba(0,0,0,0.2); }
.association_holder img { max-width: 80px; max-height: 80px; object-fit: cover; border-radius: 8px; margin-bottom: 10px; }
.association_holder h4 { font-size: 16px; color: #333; text-align: center; }
.association_holder p { font-size: 13px; color: #666; margin: 5px 0; }
.action_btn { display: flex; gap: 5px; position: absolute; top: 8px; right: 8px; }
.action_btn button { width: 28px; height: 28px; border: none; border-radius: 6px; display: flex; justify-content: center; align-items: center; cursor: pointer; }
.btn-success { background: seagreen; color: #fff; }
.btn-danger { background: red; color: #fff; }
.no_data { width: 100%; text-align: center; color: #666; padding: 20px; }

@media (max-width: 992px) { .association_holder { flex: 1 1 calc(50% - 20px); max-width: calc(50% - 20px); } }
@media (max-width: 600px) { .association_holder { flex: 1 1 100%; max-width: 100%; } }
</style>

<script>
$(document).ready(function () {

    // Function to reload the current association programs
    function loadFocusAssociation(ass_id, message = "") {
        if (!ass_id) return;
        $.get(`focus_association.php?ass_id=${ass_id}`, function (html) {
            // Show alert first, then reload content
            if (message) alert(message);

            $(".content-main").remove();
            $(".content").hide();
            $(".content").html(`<div class="content-main">${html}</div>`).fadeIn(200);
        });
    }

    // Add Program
    $(document).off("click", ".btn-primary[href^='add_program.php']").on("click", ".btn-primary[href^='add_program.php']", function (e) {
        e.preventDefault();
        const url = $(this).attr("href");
        $.get(url, function (html) {
            $(".content-main").remove();
            $(".content").hide();
            $(".content").html(`<div class="content-main">${html}</div>`).fadeIn(200);
        });
    });

    // Back to Colleges
    $(document).off("click", ".btn-secondary[href='association.php']").on("click", ".btn-secondary[href='association.php']", function (e) {
        e.preventDefault();
        $.get("association.php", function (html) {
            $(".content-main").remove();
            $(".content").hide();
            $(".content").html(`<div class="content-main">${html}</div>`).fadeIn(200);
        });
    });
    // View players in a program
$(document).off("click", ".association_holder").on("click", ".association_holder", function (e) {
    if ($(e.target).closest(".action_btn").length) return; // ignore button clicks

    const prog_id = $(this).data("prog_id");
    if (!prog_id) return;

    $.get(`focus_program.php?prog_id=${prog_id}`, function (html) {
        $(".content-main").remove();
        $(".content").hide();
        $(".content").html(`<div class="content-main">${html}</div>`).fadeIn(200);
    });
});


    // Edit Program
    $(document).off("click", ".edit_prog").on("click", ".edit_prog", function(e){
        e.preventDefault();
        const prog_id = $(this).data("id");
        if(!prog_id) return;
        $.get(`edit_program.php?prog_id=${prog_id}`, function(html){
            $(".content-main").remove();
            $(".content").hide();
            $(".content").html(`<div class="content-main">${html}</div>`).fadeIn(200);
        });
    });

    // Delete Program
    $(document).off("click", ".delete_prog").on("click", ".delete_prog", function (e) {
        e.preventDefault();
        const prog_id = $(this).data("id");
        if (!prog_id) return;

        if (!confirm("Are you sure you want to delete this program?")) return;

        // Get the parent association ID from a data attribute on the container
        const ass_id = $(this).closest(".main").find(".btn-primary").attr("href")?.split("ass_id=")[1];
        if (!ass_id) {
            alert("College ID not found.");
            return;
        }

        $.post("delete_program.php", { prog_id: prog_id }, function (res) {
            try { if (typeof res === "string") res = JSON.parse(res); } catch (err) {}

            if (res && res.success) {
                // Show alert and reload
                loadFocusAssociation(ass_id, res.message || "Program deleted successfully.");
            } else {
                alert((res && res.message) ? res.message : "Failed to delete program.");
            }
        }, "json").fail(function () {
            alert("Request failed. Check server logs or network.");
        });
    });

});
</script>

