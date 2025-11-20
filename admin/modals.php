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
                        <input type="text" class="form-control" name="e_title" value="<?php echo $event_details['ev_name']?>" required>
                    </div>
                    <div class="form-group">
                        <label for="e_description">Description:</label>
                        <input type="text" class="form-control" name="e_description" value="<?php echo $event_details['ev_description']?>" required>
                    </div>
                    <div class="form-group">
                        <label for="e_venue">Venue:</label>
                        <input type="text" class="form-control" name="e_venue" value="<?php echo $event_details['ev_address']?>" required>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5 col-md-offset-1">
                            <label for="e_start">From:</label>
                            <input type="date" class="form-control" name="e_start" value="<?php echo $event_details['start']?>" required>
                        </div>
                        <div class="col-md-5 col-md-offset-1">
                            <label for="e_end">To:</label>
                            <input type="date" class="form-control" name="e_end" value="<?php echo $event_details['end']?>" required>
                        </div>
                        <input type="hidden" name="e_id" value="<?php echo $event_details['ev_id'] ?>">
                    </div>
                    <div class="clearfix"></div>
                </form>
            </div>
            <div class="modal-footer">
                <div class="pull-right">
                    <button class="btn btn-secondary close-modal">
                        Close
                    </button>
                    <button class="btn btn-danger btn_trash">
                        Delete
                    </button>
                </div>
            <?php if($modify_ev){ ?>
                <div class="pull-right">
                    <button class="btn btn-success btn_save" data_form="#update_form_event">
                        Update
                    </button>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="Mymodal_add_event" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-head well">
                <h3>New Event</h3>
            </div>
            <div class="modal-body">
                <form action="#" id="add_form_event">
                    <div class="form-group">
                        <label for="e_title">Title:</label>
                        <input type="text" class="form-control" name="e_title" required>
                    </div>
                    <div class="form-group">
                        <label for="e_description">Description:</label>
                        <textarea class="form-control" name="e_description" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="e_venue">Venue:</label>
                        <input type="text" class="form-control" name="e_venue" required>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5 col-md-offset-1">
                            <label for="e_start">From:</label>
                            <input type="date" class="form-control" name="e_start" required>
                        </div>
                        <div class="col-md-5 col-md-offset-1">
                            <label for="e_end">To:</label>
                            <input type="date" class="form-control" name="e_end" required>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                </form>
            </div>
            <div class="modal-footer">
                <div class="pull-right">
                    <button class="btn btn-success btn_save" data_form="#add_form_event">
                        Save
                    </button>
                </div>
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
// forms submits
$(".btn_trash").click(function(){
    if(confirm("Are you sure you want to delete this event?")){
        $.post("../action.php?a=deleteEvent",{id:<?= $event_details['ev_id'] ?>},function(e){
            console.log(e)
            if(e.status){
                window.location.href = window.location.origin + window.location.pathname;
            }else{
                alert(e.message);
            }
        })
    }
})
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
                window.location.href = "?e_id="+e.message;
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
});
// Open Profile Modal
$('#profileBtn').click(function(){
    $('#Mymodal_profile').modal('show');
});

// Handle Profile Form Submission
$('#profile_form').submit(function(e){
    e.preventDefault();
    $.ajax({
        url: '../action.php?a=update_profile',
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