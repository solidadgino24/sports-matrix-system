<?php
$ev_id = $_SESSION['ev_id'];
?>

<!-- Update Event Modal -->
<div class="modal fade" id="Mymodal_update_event" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-head well">
                <h3><?php echo $event_status ?></h3>
            </div>
            <div class="modal-body">
                <form id="update_form_event">
                    <div class="form-group">
                        <label>Title:</label>
                        <input readonly type="text" class="form-control" name="e_title" value="<?php echo $event_details['ev_name']?>" required>
                    </div>
                    <div class="form-group">
                        <label>Description:</label>
                        <input readonly type="text" class="form-control" name="e_description" value="<?php echo $event_details['ev_description']?>" required>
                    </div>
                    <div class="form-group">
                        <label>Venue:</label>
                        <input readonly type="text" class="form-control" name="e_venue" value="<?php echo $event_details['ev_address']?>" required>
                    </div>
                    <div class="form-group row">
                        <div class="col-md-5">
                            <label>From:</label>
                            <input readonly type="date" class="form-control" name="e_start" value="<?php echo $event_details['start']?>" required>
                        </div>
                        <div class="col-md-5">
                            <label>To:</label>
                            <input readonly type="date" class="form-control" name="e_end" value="<?php echo $event_details['end']?>" required>
                        </div>
                        <input type="hidden" name="e_id" value="<?php echo $event_details['ev_id'] ?>">
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Application Modal -->
<div class="modal fade" id="Mymodal_app_form" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-head well"><h3>Application Form</h3></div>
            <div class="modal-body">
                <form id="app_form_tourna">
                    <div class="form-group">
                        <label>Qualification:</label><br>
                        <span class="btn btn_check_qualification">Check Qualification</span>
                        <p class="form-control a_qualification" style="display:none;"></p>
                    </div>
                    <div class="form-group">
                        <label>Jersey Number:</label>
                        <input type="text" class="form-control" name="a_jersey_number" required>
                    </div>
                    <input type="hidden" class="hidden_id" name="user_id" value="<?php echo $user_id; ?>">
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-success btn_save" data_form="#app_form_tourna">Save</button>
            </div>
        </div>
    </div>
</div>

<!-- Profile Modal -->
<div class="modal fade" id="Mymodal_profile" tabindex="-1" role="dialog" aria-labelledby="profileModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <form id="profile_form" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-head well"><h3>Edit Profile</h3></div>
                <div class="modal-body row g-4">
                    <?php
                        $profile_sql = $con->prepare("
                            SELECT p.*, u.status, u.email AS user_email, S.ass_id AS association, S.prog_id AS program, S.year_level 
                            FROM tbl_profile AS p
                            LEFT JOIN tbl_user AS u ON p.user_id=u.user_id
                            LEFT JOIN tbl_association_players AS S ON u.user_id=S.user_id
                            WHERE u.user_id = ?
                        ");
                        $profile_sql->bind_param("i", $user_id);
                        $profile_sql->execute();
                        $profile_result = $profile_sql->get_result();
                        $profile = $profile_result->fetch_assoc();
                    ?>
                    <div class="col-md-4 text-center">
                        <div class="profile-preview mb-3">
                            <img src="<?php echo isset($profile['profile']) ? "data:image/png;base64,".$profile['profile'] : '#'; ?>" alt="Profile" style="max-width:150px; border-radius:50%;">
                        </div>
                        <input type="file" class="form-control" name="profile" accept=".png,.jpg,.jpeg">
                    </div>
                    <div class="col-md-8">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label>First Name</label>
                                <input type="text" class="form-control" name="first_name" value="<?php echo htmlspecialchars($profile['first_name'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label>Middle Name / Initial</label>
                                <input type="text" class="form-control" name="middle_name" value="<?php echo htmlspecialchars($profile['middle_name'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="row g-3 mt-1">
                            <div class="col-md-6">
                                <label>Last Name / Surname</label>
                                <input type="text" class="form-control" name="last_name" value="<?php echo htmlspecialchars($profile['last_name'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label>Suffix</label>
                                <input type="text" class="form-control" name="suffix" value="<?php echo htmlspecialchars($profile['suffix'] ?? ''); ?>" placeholder="Optional">
                            </div>
                        </div>
                        <div class="row g-3 mt-1">
                            <div class="col-md-6">
                                <label>Gender</label>
                                <select class="form-select" name="gender" required>
                                    <option value="1" <?php if(($profile['gender'] ?? '')==1) echo 'selected'; ?>>Male</option>
                                    <option value="2" <?php if(($profile['gender'] ?? '')==2) echo 'selected'; ?>>Female</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label>Birthdate</label>
                                <input type="date" class="form-control" name="birthday" value="<?php echo htmlspecialchars($profile['birthday'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="row g-3 mt-1">
                            <div class="col-md-6">
                                <label>Contact Number</label>
                                <input type="text" class="form-control" name="contact" value="<?php echo htmlspecialchars($profile['contact'] ?? ''); ?>" placeholder="09XXXXXXXXX" maxlength="11" pattern="^09\d{9}$" required>
                            </div>
                            <div class="col-md-6">
                                <label>Email</label>
                                <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($profile['user_email'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="row g-3 mt-1">
                            <div class="col-md-6">
                                <label>Association</label>
                                <select name="ass_id" id="profile_association" class="form-select" disabled required>
                                    <option selected disabled>--SELECT--</option>
                                    <?php
                                        $assocs = $con->query("SELECT ass_id, name FROM tbl_association");
                                        while($a = mysqli_fetch_assoc($assocs)){
                                            $sel = ($profile['association']==$a['ass_id'])?"selected":""; 
                                            echo "<option value='{$a['ass_id']}' $sel>{$a['name']}</option>";
                                        }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label>Program</label>
                                <select name="prog_id" id="profile_program" class="form-select" disabled required>
                                    <option selected disabled>--SELECT PROGRAM--</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-3">
                            <label>Year Level</label>
                            <select name="year_level" class="form-select" disabled required>
                                <option selected disabled>--SELECT YEAR LEVEL--</option>
                                <?php
                                    $years = ["1st Year","2nd Year","3rd Year","4th Year","5th Year"];
                                    foreach($years as $y){
                                        $sel = ($profile['year_level']==$y)?"selected":""; 
                                        echo "<option value='$y' $sel>$y</option>";
                                    }
                                ?>
                            </select>
                        </div>
                        <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Save Changes</button>
                    <button type="button" class="btn btn-secondary close-modal" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
.btn_check_qualification{
    border: 2px solid #131619;
    color: #131619;
    cursor:pointer;
}
.btn_check_qualification:hover{
    background: #131619;
    color:white;
    border: 2px solid white;
}
.a_qualification {
    margin-top: 8px;
    padding: 8px 10px;
    border-radius: 6px;
    display: inline-block;
}
.profile-preview img{
    max-width:150px;
    border-radius:50%;
}
</style>

<script>
$(".close-modal").click(function(){
    $(".modal").modal("hide");
});

// Load programs dynamically
function loadPrograms(ass_id, selectedProg = null){
    if(!ass_id) return;
    $.get("load_programs.php", { ass_id: ass_id }, function(response){
        $("#profile_program").html(response);
        if(selectedProg) $("#profile_program").val(selectedProg);
    });
}

$('#Mymodal_profile').on('show.bs.modal', function(){
    var savedAssoc = $("#profile_association").val();
    var savedProg = "<?php echo $profile['program'] ?? ''; ?>";
    loadPrograms(savedAssoc, savedProg);
});

$("#profile_association").on("change", function(){
    loadPrograms($(this).val());
});

// Contact validation
$("input[name='contact']").on("input", function() {
    this.value = this.value.replace(/[^0-9]/g, "").slice(0,11);
});

// Profile image preview
$("#profile_form input[name='profile']").change(function(){
    var reader = new FileReader();
    reader.onload = e => $(this).siblings(".profile-preview").find("img").attr("src", e.target.result);
    reader.readAsDataURL(this.files[0]);
});

// Submit profile via AJAX
$('#profile_form').submit(function(e){
    e.preventDefault();
    var formData = new FormData(this);
    $.ajax({
        url: '../action.php?a=update_profile',
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        dataType: 'json',
        success: function(res){
            if(res.status){
                alert('Profile updated successfully!');
                location.reload();
            } else {
                alert(res.message);
            }
        },
        error: function(xhr){
            alert("AJAX error: " + xhr.responseText);
        }
    });
});

// Application form submission
$("#app_form_tourna").submit(function(e){
    e.preventDefault();
    $.post("../action.php?a=apply_tourna", $(this).serialize(), function(res){
        if(res.status){
            $("#Mymodal_app_form").modal("hide");
            $.get("focus_tournament.php?id="+$(".hidden_id").val(), function(e){
                $(".content-main").remove();
                $(".content").hide();
                $(".content").html(`<div class="content-main">${e}</div>`).fadeIn(200);
                $(".form-control").val("");
            });
        } else {
            alert(res.message);
        }
    });
});

// Check Qualification
$(".btn_check_qualification").click(function(){
    let btn = $(this);
    btn.text("Checking...");
    $.get("../list.php?s=check_qualification", { user_id: $(".hidden_id").val() }, function(res){
        btn.text("Check Qualification");
        let msgBox = btn.parent().find(".a_qualification");
        if(res.status){
            if(res.data?.qualified){
                msgBox.show().html("<b>You are qualified!</b>").css({"border":"1px solid green","color":"green","background":"#eaffea"});
            } else {
                let reasonText = res.data?.reason ? `<div style='font-size:13px;color:#444;'>Reason: ${res.data.reason}</div>` : "";
                msgBox.show().html(`<b>You are not qualified!</b>${reasonText}`).css({"border":"1px solid red","color":"red","background":"#ffecec"});
            }
        } else {
            alert("Error checking qualification.");
        }
    }, "json");
});

// Update Event form
$("#update_form_event").submit(function(e){
    e.preventDefault();
    $.post("../action.php?a=editEvent", $(this).serialize(), function(res){
        if(res.status) location.reload();
        else alert(res.message);
    });
});

// Save buttons
$(".btn_save").click(function(){
    $($(this).attr("data_form")).submit();
});
</script>
