<div class="modal fade" id="Mymodal_update_event" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-head well">
            <h3><?php echo $event_status ?></h3>
            </div>
            <div class="modal-body">
                <form action="#" id="update_form_event">
                    <div class="form-group">
                        <label for="e_title">Title:</label>
                        <input readonly type="text" class="form-control" name="e_title" value="<?php echo $event_details['ev_name']?>" required>
                    </div>
                    <div class="form-group">
                        <label for="e_description">Description:</label>
                        <input readonly type="text" class="form-control" name="e_description" value="<?php echo $event_details['ev_description']?>" required>
                    </div>
                    <div class="form-group">
                        <label for="e_venue">Venue:</label>
                        <input readonly type="text" class="form-control" name="e_venue" value="<?php echo $event_details['ev_address']?>" required>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5 col-md-offset-1">
                            <label for="e_start">From:</label>
                            <input readonly type="date" class="form-control" name="e_start" value="<?php echo $event_details['start']?>" required>
                        </div>
                        <div class="col-md-5 col-md-offset-1">
                            <label for="e_end">To:</label>
                            <input readonly type="date" class="form-control" name="e_end" value="<?php echo $event_details['end']?>" required>
                        </div>
                        <input type="hidden" name="e_id" value="<?php echo $event_details['ev_id'] ?>">
                    </div>
                    <div class="clearfix"></div>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="schedule_match" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <form action="#" id="schedule_match_form">
                    <h3>Schedule Match</h3>
                    <hr>
                    <div class="form-group">
                        <label for="game_start">Game Start</label>
                        <input type="datetime-local" name="game_start" class="form-control game_start" required>
                    </div>
                    <button class="btn btn-success pull-right" type="submit">
                        Save
                    </button>
                    <div class="clearfix"></div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Profile Modal -->
<div class="modal fade" id="Mymodal_profile" tabindex="-1" role="dialog" aria-labelledby="profileModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="profile_form">
            <div class="modal-content">
                <div class="modal-head well">
                    <h3>Edit Profile</h3>
                </div>
                <div class="modal-body">
                    <?php
                        $profile_sql = $con->prepare("SELECT fullname, gender, birthday, contact, email FROM tbl_profile WHERE user_id = ?");
                        $profile_sql->bind_param("i", $user_id);
                        $profile_sql->execute();
                        $profile_result = $profile_sql->get_result();
                        $profile = $profile_result->fetch_assoc();
                    ?>
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" class="form-control" name="fullname" value="<?php echo htmlspecialchars($profile['fullname'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Gender</label>
                        <select class="form-control" name="gender" required>
                            <option value="1" <?php if(($profile['gender'] ?? '')==1) echo 'selected'; ?>>Male</option>
                            <option value="2" <?php if(($profile['gender'] ?? '')==2) echo 'selected'; ?>>Female</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Birthday</label>
                        <input type="date" class="form-control" name="birthday" value="<?php echo htmlspecialchars($profile['birthday'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Contact</label>
                        <input type="text" class="form-control" name="contact" value="<?php echo htmlspecialchars($profile['contact'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($profile['email'] ?? ''); ?>" required>
                    </div>
                    <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Save Changes</button>
                    <button type="button" class="btn btn-secondary close-modal" data-dismiss="modal">Close</button>
                </div>
            </div>
        </form>
    </div>
</div>
<style>
.btn_check_qualification{
    border: 2px solid #131619;
    color: #131619;
}
.btn_check_qualification:hover{
    background: #131619;
    color:white;
    border: 2px solid white;
}
</style>
<script>
$(".close-modal").click(function(){
    $(".modal").modal("hide");
})
$(".btn_save").click(function(){
    $($(this).attr("data_form")).submit();
});

$(".form-control").change(function(){
    $(this).css("border-color","");
});
$(".btn_check_qualification").click(function(){
    let btn = $(this);
    btn.text("checking..");
    $.get("../list.php?s=check_qualification",function(res){
        if(res.status){
            btn.hide();
            if(res.data){
                btn.parent().find(".a_qualification").show().text("You are qualified!").css("border","1px solid green").css("color","green");
            }else{
                btn.parent().find(".a_qualification").show().text("You are not qualified!").css("border","1px solid red").css("color","red");;
            }
        }
    })
})

// forms submits

$("#add_form_event").submit(function(e){
    e.preventDefault();
    let submit = true;
    $(this).find(".form-control").each(function(){
        let value = $(this).val() 
        if(value == null || value ==""){
            $(this).css("border-color","red");
            submit = false;
        }
    });
    if(submit){
        $.post("../action.php?a=newEvent",$(this).serialize(),function(e){
            if(e.status){
                location.reload();
            }else{
                alert(e.message)
            }
        })
    }
});
$("#update_form_event").submit(function(e){
    e.preventDefault();
    let submit = true;
    $(this).find(".form-control").each(function(){
        let value = $(this).val() 
        if(value == null || value ==""){
            $(this).css("border-color","red");
            submit = false;
        }
    });
    if(submit){
        $.post("../action.php?a=editEvent",$(this).serialize(),function(e){
            console.log(e);
            if(e.status){
                location.reload();
            }else{
                alert(e.message)
            }
        })
    }
})
$("#app_form_tourna").submit(function(e){
    e.preventDefault();
    $.post("../action.php?a=apply_tourna",$(this).serialize(),function(res){
        console.log(res);
        if(res.status){
            $("#Mymodal_app_form").modal("hide");
            $.get("focus_tournament.php?id="+$(".hidden_id").val(),function(e){
                $(".content-main").remove();
                $(".content").hide();
                $(".content").html(`<div class="content-main">${e}</div>`).fadeIn(200);
                $(".form-control").val("");
            });
        }else{
            alert(res.message);
        }
    })
});
// Open Profile Modal
$('#profileBtn').click(function(){
    $('#Mymodal_profile').modal('show');
});

// Handle Profile Form Submission
$('#profile_form').submit(function(e){
    e.preventDefault();
    $.ajax({
        url: '../action.php?a=update_profile_ass',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(res){
            if(res.status){
                alert('Profile updated!');
                location.reload();
            }else{
                alert(res.message);
            }
        },
        error: function(xhr){
            alert("AJAX error: " + xhr.responseText);
        }
    });
});
</script>