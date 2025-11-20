<?php
include "../conn.php";
include "head.php";

// Fetch user info from session
$user_id = $_SESSION['session_user_id'] ?? null;
$user_type = $_SESSION['session_type'] ?? null;

if($user_id){
    $user_sql = $con->prepare("SELECT fullname FROM tbl_profile WHERE user_id = ?");
    $user_sql->bind_param("i", $user_id);
    $user_sql->execute();
    $user_result = $user_sql->get_result();
    $user = $user_result->fetch_assoc();
}

// Map user type number to name
$user_type_names = [
    1 => "Admin",
    2 => "Association",
    3 => "Player",
    4 => "Scorer"
];
$user_type_name = $user_type_names[$user_type] ?? "User";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SYSTEM | Dashboard</title>
    <?php include "header.html";?>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            display: flex;
            height: 100vh;
            background-color: #f4f4f4;
            color: #333;
        }

        .sidebar {
            width: 250px;
            background-color: #272c33;
            color: #fff;
            transition: transform 0.3s ease;
            transform: translateX(0);
            position: fixed;
            height: 100%;
            padding: 15px;
            overflow-y: auto;
            z-index: 10;
        }

        .sidebar.hide {
            transform: translateX(-250px);
        }

        .sidebar h3 {
            text-align: center;
            margin-bottom: 40px;
            color: #fff;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
        }

        .sidebar ul li {
            margin: 15px 5px;
            padding: 6px 10px;
            border-radius: 5px;
            border: 1px solid #333;
            cursor: pointer;
            font-size: 14px;
        }

        .sidebar ul li a {
            color: #fff;
            text-decoration: none;
            margin-left: 10px;
        }
        .main-content {
            flex-grow: 1;
            margin-left: 250px;
            transition: margin-left 0.3s ease;
            padding: 10px;
            width: 100%;
        }

        .main-content.collapsed {
            margin-left: 0;
        }

        .toggle-btn {
            background-color: #333;
            color: white;
            border: none;
            padding: 10px 15px;
            cursor: pointer;
            border-bottom-right-radius: 10px;
            border-top-right-radius: 10px;
            position: fixed;
            top: 20px;
            left: 250px;
            z-index: 11;
            transition: left 0.3s ease;
        }

        .toggle-btn.collapsed {
            left: 0;
        }

        .toggle-btn:hover {
            background-color: #555; 
        }

        .header {
            font-size: 24px;
            margin-bottom: 20px;
            color: #333;
            margin-left: 50px;
        }

        .content {
            background-color: #fff; 
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }
        .sidebar ul li:hover {
            border: 1px solid black;
            background: black;
        }
        .active{
            border: 1px solid black;
            background: black;
        }
        .header_event{
            font-size:24px;
            margin-left: 60px;
        }
        #dropdownMenu1{
            border: none;
            background-color: transparent;
        }
        .header_event #profileBtn{
        background-color: hsla(0, 0%, 50%, 1.00);
        float: right; 
        margin-right: 20px;
        padding: 5px;
        color: white;
        }
        .header_event #profileBtn:hover{
        background-color: hsla(0, 0%, 50%, .8); 
        }
        #accounts-menu {
    position: relative;
}

#accounts-menu .account-notif-badge {
    position: absolute;
    background: #e35f26;
    color: #fff;
    border-radius: 50%;
    font-size: 12px;
    width: 18px;
    height: 18px;
    display: flex;
    justify-content: center;
    align-items: center;
    top: 8px;
    right: 18px;
    visibility: hidden; /* Hidden if count = 0 */
}
    </style>
</head>
<body>
<?php include "modals.php" ?>
    <div class="sidebar" id="sidebar">
        <h3>Dashboard Menu</h3>
        <ul class="nav-ul">
            <li data-href="home.php"><span class='glyphicon glyphicon-home'></span> <a href="#">Home</a></li>
            <li data-href="association.php" ><span class='glyphicon glyphicon-user'></span> <a href="#" >Colleges</a></li>
            <li data-href="sports.php" ><span class='glyphicon glyphicon-flag'></span> <a href="#" >Sports</a></li>
            <li data-href="tournament.php" > <span class='glyphicon glyphicon-random'></span> <a href="#">Tournament</a></li>
            <li data-href="accounts.php" id="accounts-menu">
    <span class='glyphicon glyphicon-star-empty'></span>
    <a href="#">Accounts</a>
    <span id="account-notif-count" class="account-notif-badge"></span>
</li>

            <li data-href="generate.php" ><span class='glyphicon glyphicon-book'></span> <a href="#" >Reports</a></li>
            <li data-href="logout.php"><span class='glyphicon glyphicon-log-out'></span> <a href="#">Logout</a></li>
        </ul>
    </div>

    <div class="main-content" id="main-content">
        <button class="toggle-btn" id="toggle-btn">☰</button>
        <div class="header_event">

        <!-- Profile Button -->
    <button id="profileBtn" class="btn btn-profile">
    <span class="glyphicon glyphicon-user"></span> <span style="font-weight: bold;">
        <?php echo htmlspecialchars($user['fullname'] ?? $user_type_name); ?> 
    </span>
</button>
            
            Welcome to, <label id="event_details"><?php echo $event_details['ev_name'] ?></label>
            <label><div class="dropdown">
                <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                    <span class='glyphicon glyphicon-cog'></span>
                </button>
                <ul class="dropdown-menu" aria-labelledby="dropdownMenu1">

                    <li role="separator" class="divider"></li>
                    <?php 
                        $sql = $con->query("SELECT ev_id,ev_name FROM tbl_event ORDER BY ev_id ASC");
                        if(mysqli_num_rows($sql) > 0){
                            while($row=mysqli_fetch_assoc($sql)){
                    ?>
                        <li><a href="index.php?e_id=<?php echo $row['ev_id'] ?>"><?php echo $row['ev_name'] ?> <?php echo $span_data = ($row['ev_id']==$event_details['ev_id'])? "<span class='glyphicon glyphicon-pushpin'></span>" :" " ?></a></li>
                        <li role="separator" class="divider"></li>
                        <?php }
                        }else{
                            echo "<script>$('#Mymodal_add_event').modal()</script>";
                        } ?>
                        
                    <li><center><button class='btn btn-success' onclick="$('#Mymodal_add_event').modal()">Add Event</button></center></li>
                    <li role="separator" class="divider"></li>
                </ul>
                </div>
            </label>
        </div>
        <div class="content">
        </div>
    </div>
    <?php
    $pending_accounts = 0;

    $verify_sql = $con->query("
    SELECT COUNT(*) AS total
    FROM tbl_user AS u
    WHERE u.status = '0'
    AND (u.user_type = '2' OR u.user_type = '4')
");
if ($verify_sql && $row = $verify_sql->fetch_assoc()) {
    $pending_accounts = (int)$row['total'];
}

    ?>
<script>
const toggleBtn = document.getElementById('toggle-btn');
const sidebar = document.getElementById('sidebar');
const mainContent = document.getElementById('main-content');

toggleBtn.addEventListener('click', () => {
    sidebar.classList.toggle('hide');
    mainContent.classList.toggle('collapsed');
    toggleBtn.classList.toggle('collapsed');
});
$(document).ready(function(){

    const accountNotifCount = <?php echo $pending_accounts; ?>;
if (accountNotifCount > 0) {
    $("#account-notif-count")
        .text(accountNotifCount)
        .css("visibility", "visible");
}
    let data_table;
    let name = $(".header").find(".user_fname").text();
    if(localStorage.getItem('hreflink') == null || localStorage.getItem('hreflink') == "logout.php"){
        localStorage.setItem('hreflink',"home.php");
    }

    $.get(localStorage.getItem('hreflink'),function(e){
        $(".content-main").remove();
        $(".content").hide();
        $(".content").html(`<div class="content-main">${e}</div>`).fadeIn(200);
    })
    
    $(".nav-ul > li").click(function(){
        let nav = $(this);
        getPage(nav,true);
    });

    $(document).on("click", "#event_details", function() {
    $("#Mymodal_update_event").modal("show");
});


    $("#dropdownMenu1").on("click", function (e) {
    e.stopPropagation(); // Prevents sidebar or other elements from closing it
    const $menu = $(this).next(".dropdown-menu");
    $menu.toggle(); // Toggles manually in all viewports
    $(".dropdown-menu").not($menu).hide(); // Hide other dropdowns if any
});

$(document).on("click touchstart", function (e) {
    if (!$(e.target).closest(".dropdown").length) {
        $(".dropdown-menu").hide(); // Hide if clicking outside
    }
});

    function getPage(nav,access){
        let link = nav.attr("data-href");
        if(access){
            //if(link!=localStorage.getItem('hreflink')){
                localStorage.setItem('hreflink',link);
                $(".nav-ul > li").removeClass("active");
                nav.addClass("active");
                if(link == "logout.php"){
                    window.location.href=link;
                }else if(link == "generate.php"){
    // Load generate.php into main content area
    $.get(link, function(e){
        $(".content-main").remove();
        $(".content").hide();
        $(".content").html(`<div class="content-main">${e}</div>`).fadeIn(200);
    });

                }else{
                    if(link!=undefined){
                        $.get(link,function(e){
                            $(".content-main").remove();
                            $(".content").hide();
                            $(".content").html(`<div class="content-main">${e}</div>`).fadeIn(200);
                        })
                    }
                }
            //}
        }else{
            $(".nav-ul > li").removeClass("active");
            nav.addClass("active");
            if(link == "logout.php"){
                localStorage.setItem('link',null);
                window.location.href=link;
            }else{
                if(link!=undefined){
                    $.get(link,function(e){
                        $(".content-main").remove();
                        $(".content").hide();
                        $(".content").html(`<div class="content-main">${e}</div>`).fadeIn(200);
                    })
                }
            }
        }
    }
    $(document).on("click",".add_ass_btn",function(){
        $.get("add_association.php",function(e){
            $(".content-main").remove();
            $(".content").hide();
            $(".content").html(`<div class="content-main">${e}</div>`).fadeIn(200);

            $("#logo").change(function(){
                var image = this.files[0];
                let logo =$(".preview > img");
                logo.hide();
                var reader = new FileReader();

                reader.onload = function(e){
                    $(".preview > h3").hide();
                    logo.show("100");
                    logo.attr("src",e.target.result);
                }
                reader.readAsDataURL(image);
            });

            
            $("#add_ass_form").submit(function(e){
                e.preventDefault();
                let cont = true;
                $("#add_ass_form > .form-group > .form-control").each(function(){
                    if($(this).val() == null || $(this).val() == ""){
                        $(this).css("border-color","red");
                        cont = false
                    }
                });
                if(cont){
                    var formData = new FormData();
                    formData.append('logo', $("#logo")[0].files[0]);
                    formData.append('desc', $("#desc").val());
                    formData.append('name', $("#name").val());

                    $.ajax({
                        url: '../action.php?a=add_ass',
                        type: 'POST',
                        data: formData,
                        contentType: false,
                        processData: false,
                        success: function(e) {
                            if(e.status){
                                alert("saved");
                                $.get("association.php",function(e){
                                    $(".content-main").remove();
                                    $(".content").hide();
                                    $(".content").html(`<div class="content-main">${e}</div>`).fadeIn(200);
                                });
                            }else{
                                alert(e.message);
                            }
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            alert('File upload failed: ' + textStatus + ' - ' + errorThrown);
                        }
                    });
                }else{
                    alert("Feild is required!");
                }

                $(".form-group > .form-control").change(function(){
                    $(this).css("border-color","");
                })
            })
        })
    });
    
    $(document).on("click",".add_sport_btn",function(){
        $.get("add_sport.php",function(e){
            $(".content-main").remove();
            $(".content").hide();
            $(".content").html(`<div class="content-main">${e}</div>`).fadeIn(200);

            $(".page1_nav > span").click(function(){
                let cont = true;
                $(".page1 > .form-group > .form-control").each(function(){
                    if($(this).val() == null || $(this).val() == ""){
                        $(this).css("border-color","red");
                        cont = false
                    }
                })

                if(cont){
                    $(this).parent().hide();
                    $(".page2_nav").show();
                    $("#add_sport_form > .page1").hide()
                    $("#add_sport_form > .page2").show()
                }else{
                    alert("Feild is required!");
                }
                
            });

            $(".page2_nav > span").click(function(){
                $(this).parent().hide();
                $(".page1_nav").show();
                $("#add_sport_form > .page2").hide()
                $("#add_sport_form > .page1").show()
            });

            $(".form-group > .form-control").change(function(){
                $(this).css("border-color","");
            })

            $(document).on("change",".scoring",function(){
                if($(this).val()==1){
                    $(this).parent().parent().find(".point_opt").show();
                    $(this).parent().parent().find(".set_opt").hide();
                }else{
                    $(this).parent().parent().find(".point_opt").hide();
                    $(this).parent().parent().find(".set_opt").show();
                }
            });
            $("#add_game_modes").click(function(){
                $(".game_modes").append(`
                        <div class="game_mode">
                            <i class="remove">X</i>
                            <div class="form-group">
                                <label for="name">Name</label>
                                <input type="text" id="name_mode" class="form-control name_mode">
                            </div>
                            <div class="form-group">
                                <label for="player">Players</label>
                                <input type="number" id="player" class="form-control player">
                            </div>
                            <div class="form-group">
                                <label for="category">Category</label>
                                <select id="category" class="form-control category" required>
                                    <option disabled selected>-- SELECT --</option>
                                    <option value="1">Girls</option>
                                    <option value="2">Boys</option>
                                    <option value="3">Mixed</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="scoring">Scoring</label>
                                <select id="scoring" class="form-control scoring" required>
                                    <option disabled selected>-- SELECT --</option>
                                    <option value="1">Point based</option>
                                    <option value="2">Set based</option>
                                </select>
                            </div>
                            <div class="point_opt" style="display:none;">
                                <div class="form-group">
                                    <label for="quarters">Quarter('s)</label>
                                    <input type="number" id="quarters" class="form-control quarters">
                                </div>
                            </div>
                            <div class="set_opt" style="display:none;">
                                <div class="form-group">
                                    <label for="points">Match point</label>
                                    <input type="number" id="points" class="form-control points">
                                </div>
                                <div class="form-group">
                                    <label for="game_set">Sets('s)</label>
                                    <input type="number" id="game_set" class="form-control game_set">
                                </div>
                            </div>
                        </div>
                        `);
                $(".game_mode > .remove").click(function(){
                    $(this).parent().remove();
                });
            });
            $("#add_sport_form").submit(function(e) {
    e.preventDefault();

    var formData = new FormData();
    formData.append('img', $("#img")[0].files[0]);
    formData.append('rules', $("#rules")[0].files[0]);
    formData.append('name', $("#name").val());

    $.ajax({
        url: '../action.php?a=add_sport',
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        dataType: 'json',
        beforeSend: function() {
            $("#save_sport").prop("disabled", true).text("Saving...");
        },
        success: function(res) {
            if (res.status) {
                var modes = [];

                $(".game_modes > .game_mode").each(function() {
                    let mode = $(this);
                    var data = [];
                    data.push(mode.find(".name_mode").val());
                    data.push(mode.find(".player").val());
                    data.push(mode.find(".category").val());
                    data.push(mode.find(".scoring").val());

                    if (mode.find(".scoring").val() == 1) {
                        data.push(mode.find(".quarters").val());
                    } else {
                        data.push(mode.find(".game_set").val());
                        data.push(mode.find(".points").val());
                    }

                    modes.push(data);
                });

                var jsonData = JSON.stringify(modes);

                $.post('../action.php?a=addgame_modes', { data: jsonData, id: res.id }, function(r) {
                    if (r.status) {
                        alert("✅ Sport successfully added!");
                        // Reload content or page
                        $.get("sports.php", function(content) {
                            $(".content-main").remove();
                            $(".content").hide();
                            $(".content").html(`<div class="content-main">${content}</div>`).fadeIn(200);
                        });
                    } else {
                        alert("⚠️ Failed to save game modes: " + r.message);
                    }
                }, 'json');

            } else {
                alert("❌ Error: " + res.message);
            }
        },
        error: function(xhr, textStatus, errorThrown) {
            console.error("AJAX Error:", xhr.responseText);
            alert("File upload failed: " + textStatus + " - " + errorThrown);
        },
        complete: function() {
            $("#save_sport").prop("disabled", false).text("Save");
        }
    });
});

        });
    });
    $(document).on("submit","#modify_sport_form",function(e){
        e.preventDefault();
        var formData = new FormData();
        formData.append('img', $("#img")[0].files[0]);
        formData.append('rules', $("#rules")[0].files[0]);
        formData.append('name', $("#name").val());
        $.ajax({
            url: '../action.php?a=edit_sport',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(e) {
                if(e.status){
                    $.get("sports.php",function(e){
                        $(".content-main").remove();
                        $(".content").hide();
                        $(".content").html(`<div class="content-main">${e}</div>`).fadeIn(200);
                    })
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('File upload failed: ' + textStatus + ' - ' + errorThrown);
            }
        });
    });
    $(document).on("click",".review_request",function(){
        let id = $(this).attr("data-id");
        $.get("account_review.php?acc_id="+id,function(e){
            $(".content-main").remove();
            $(".content").hide();
            $(".content").html(`<div class="content-main">${e}</div>`).fadeIn(200);
            
            $(".back_to_account").click(function(){
                $.get("accounts.php",function(e){
                    $(".content-main").remove();
                    $(".content").hide();
                    $(".content").html(`<div class="content-main">${e}</div>`).fadeIn(200);
                });
            })

            $(".verify_this_account").click(function(){
                if(confirm("Verify this Account?")){
                    $.post("../action.php?a=verify_acc",{id:id},function(e){
                        if(e.status){
                            $.get("accounts.php",function(e){
                                $(".content-main").remove();
                                $(".content").hide();
                                $(".content").html(`<div class="content-main">${e}</div>`).fadeIn(200);
                            });
                        }else{
                            alert(e.message);
                        }
                    });
                }
            })
        })
    })
   $(document).on("click", ".verify_request", function() {
    let id = $(this).attr("data-id");
    if (confirm("Verify this Account?")) {
        $.post("../action.php?a=verify_acc", { id: id }, function(e) {
            if (e.status) {
                // Show success alert
                alert("Account verified successfully!");

                // Reload accounts content
                $.get("accounts.php", function(e) {
                    $(".content-main").remove();
                    $(".content").hide();
                    $(".content").html(`<div class="content-main">${e}</div>`).fadeIn(200);
                });
            } else {
                alert(e.message);
            }
        }, "json"); // make sure response is treated as JSON
    }
});

    $(document).on("click",".Reject_request",function(){
        let id = $(this).attr("data-id");
        if(confirm("Reject this Account?")){
            $.post("../action.php?a=reject_acc",{id:id},function(e){
                if(e.status){
                    $.get("accounts.php",function(e){
                        $(".content-main").remove();
                        $(".content").hide();
                        $(".content").html(`<div class="content-main">${e}</div>`).fadeIn(200);
                    });
                }else{
                    alert(e.message);
                }
            });
        }
    });

    $(document).on("click",".sport_holder",function(){
        let sport = $(this).attr("sport_id");
        $.get("sport_focus.php?id="+sport,function(e){
            $(".content-main").remove();
            $(".content").hide();
            $(".content").html(`<div class="content-main">${e}</div>`).fadeIn(200);

            data_table = $('#liveDataTableSportsCategory').DataTable({
            ajax: {
                url: '../dataTables/sports_category.php',
                type: 'GET',
                dataSrc: function (json) {
                    if (!json.recordsTotal) {
                        console.error("Invalid response:", json);
                        return [];
                    }
                    return json.data;
                }
            },
            columns: [
                { 
                    data: 'name',
                    title: 'Name'
                },
                { 
                    data: 'players', 
                    title: 'Players'
                },
                { 
                    data: 'category', 
                    title: 'Category'
                },
                {
                    data: 'scoring',
                    title: 'Scoring'
                },
                { 
                    data: 'point_base', 
                    title: 'Points'
                },
                { 
                    data: 'sets', 
                    title: 'Sets/Quarter'
                },
                { 
                    data: 'date_added', 
                    title: 'Date Added'
                },
                {
                    data: 'game_id',
                    title: 'Options',
                    render: function (data, type, row) {
                        if (!data) return `<button disabled style="cursor:not-allowed">Edit</button>`;
                        return `
                            <button onclick="editGamemodeData(${data})">
                                Edit
                            </button>
                        `;
                    }
                }
            ],
                processing: true,
                serverSide: true,
                responsive: true,
                autoWidth: false,
                order: [[0, 'desc']]
            });
        })
    });
    $(document).on("submit","#game_mode_form",function(e){
        e.preventDefault();
        $.post("../action.php?a=game_mode",$(this).serialize(),function(res){
            console.log(res);
            if(res.status){
                alert("saved")
                $(".sport_holder").click();
            }else{
                alert(res.message);
            }
        });
    })
    $(document).on("click",".add_sport_mode_btn",function(){
        $.get("add_game_mode.php",function(e){
            $(".content-main").remove();
            $(".content").hide();
            $(".content").html(`<div class="content-main">${e}</div>`).fadeIn(200);
        })
    })
    $(document).on("change",".scoring",function(){
        if($(this).val()==1){
            $(this).parent().parent().find(".point_opt").show();
            $(this).parent().parent().find(".set_opt").hide();
        }else{
            $(this).parent().parent().find(".point_opt").hide();
            $(this).parent().parent().find(".set_opt").show();
        }
    });

    $(document).on("click", ".btn_add_tourna", function () {
    $.get("add_tournament.php", function (e) {
        $(".content-main").remove();
        $(".content").hide();
        $(".content").html(`<div class="content-main">${e}</div>`).fadeIn(200);

        let minimum = 0;

        // When sport is selected
        $("#sport").change(function () {
            $.post("../list.php?s=sport_details", { id: $(this).val() }, function (res) {
                if (res.status) {
                    $(".gm_preview").html('');
                    $(".preview")
                        .find("h3").hide()
                        .parent()
                        .find("img")
                        .attr("src", "data:image/png;base64," + res.data.img)
                        .hide()
                        .show(300);

                    $("#game_mode").find("option").remove();
                    $("#game_mode").append(`<option value="" disabled selected>--Select--</option>`);

                    for (let i = 0; i < res.data.data.length; i++) {
                        let data = res.data.data[i];
                        $("#game_mode").append(`<option value="${data.game_id}">${data.name} Category(${data.category})</option>`);
                    }
                }
            }, "json"); // ✅ ensure JSON parsing
        });

        // When game mode is selected
        $("#game_mode").change(function () {
            $.post("../list.php?s=get_player_minimum", { id: $(this).val() }, function (res) {
                console.log(res);
                if (res.status) {
                    let data = res.data;
                    minimum = data.players;
                    $(".minimum").val(minimum);
                    $(".gm_preview").html(`
                        <ul>
                            <li><label>Name: </label> <p>${data.name}</p></li>
                            <li><label>Category:</label> <p>${data.category}</p></li>
                            <li><label>Scoring:</label> <p>${data.scoring}</p></li>
                            <li><label>Players:</label> <p>${data.players} Player(s)</p></li>
                        </ul>
                    `).hide().fadeIn(300);
                }
            }, "json"); // ✅ ensure JSON parsing
        });

        // Submit Add Tournament form
        $("#add_tourna_form").submit(function (e) {
            e.preventDefault();
            if (minimum <= $(".minimum").val()) {
                if (parseInt($(".maximum").val()) >= parseInt($(".minimum").val())) {
                    $.ajax({
                        url: "../action.php?a=addtournament",
                        type: "POST",
                        data: $(this).serialize(),
                        dataType: "json", // ✅ crucial for showing alerts
                        success: function (res) {
                            console.log(res);
                            alert(res.message); // ✅ show user feedback
                            if (res.status) {
                                $.get("tournament.php", function (e) {
                                    $(".content-main").remove();
                                    $(".content").hide();
                                    $(".content").html(`<div class="content-main">${e}</div>`).fadeIn(200);
                                });
                            }
                        },
                        error: function (xhr, status, error) {
                            alert("Error: " + error);
                        }
                    });
                } else {
                    alert("Maximum must be greater than or equal to minimum players");
                }
            } else {
                alert("Invalid minimum player input");
            }
        });
    });
});


$(document).on("click", ".btn_modify_tourna", function () {
    $.get("modify_tournament.php?id=" + $(this).parent().attr("data_id"), function (e) {
        $(".content-main").remove();
        $(".content").hide();
        $(".content").html(`<div class="content-main">${e}</div>`).fadeIn(200);

        let minimum = $(".player").val();

        // Submit Modify Tournament form
        $("#modify_tourna_form").submit(function (e) {
            e.preventDefault();
            if (minimum <= $(".minimum").val()) {
                if (parseInt($(".maximum").val()) >= parseInt($(".minimum").val())) {
                    $.ajax({
                        url: "../action.php?a=modifytournament",
                        type: "POST",
                        data: $(this).serialize(),
                        dataType: "json", // ✅ parse JSON properly
                        success: function (res) {
                            console.log(res);
                            alert(res.message); // ✅ show user feedback
                            if (res.status) {
                                $.get("tournament.php", function (e) {
                                    $(".content-main").remove();
                                    $(".content").hide();
                                    $(".content").html(`<div class="content-main">${e}</div>`).fadeIn(200);
                                });
                            }
                        },
                        error: function (xhr, status, error) {
                            alert("Error: " + error);
                        }
                    });
                } else {
                    alert("Maximum must be greater than or equal to minimum players");
                }
            } else {
                alert("Invalid minimum player input");
            }
        });
    });
});

    $(document).on("click",".btn_view_tourna",function(){
        $.get("focus_tournament.php?id="+$(this).parent().attr("data_id"),function(e){
            $(".content-main").remove();
            $(".content").hide();
            $(".content").html(`<div class="content-main">${e}</div>`).fadeIn(200);
        })
    });
    $(document).on("click",".team_btn",function(){
        $.get("team_focus.php?id="+$(this).attr("team_id"),function(e){
            $(".content-main").remove();
            $(".content").hide();
            $(".content").html(`<div class="content-main">${e}</div>`).fadeIn(200);
        });
    });
    $(document).on("click",".back_team",function(){
        $.get("focus_tournament.php",function(e){
            $(".content-main").remove();
            $(".content").hide();
            $(".content").html(`<div class="content-main">${e}</div>`).fadeIn(200);
        });
    });
    $(document).on("click",".association_holder .btn-danger delete_ass",function(){
        let data = $(this).parent().parent();
        if(confirm('Are you sure you want to delete "'+data.attr("title")+'"?')){
            $.post("../action.php?a=delete_ass",{id:data.attr("ass_id")},function(res){
                if(res.status){
                    data.fadeOut(300);
                }
            })
        }
    });
    $(document).on("click",".association_holder .btn-success edit_ass",function(){
        let id = $(this).parent().parent().attr("ass_id");
        $.get("modify_association.php?id="+id,function(e){
            $(".content-main").remove();
            $(".content").hide();
            $(".content").html(`<div class="content-main">${e}</div>`).fadeIn(200);

            $("#logo").change(function(){
                var image = this.files[0];
                let logo =$(".preview > img");
                logo.hide();
                var reader = new FileReader();

                reader.onload = function(e){
                    logo.show("100");
                    logo.attr("src",e.target.result);
                }
                reader.readAsDataURL(image);
            });

            $("#mod_ass_form").submit(function(e){
                e.preventDefault();
                let cont = true;
                $("#mod_ass_form > .form-group > .formrequire").each(function(){
                    if($(this).val() == null || $(this).val() == ""){
                        $(this).css("border-color","red");
                        cont = false
                    }
                });
                if(cont){
                    var formData = new FormData();
                    formData.append('logo', $("#logo")[0].files[0]);
                    formData.append('desc', $("#desc").val());
                    formData.append('name', $("#name").val());
                    formData.append('id', $("#id").val());

                    $.ajax({
                        url: '../action.php?a=mod_ass',
                        type: 'POST',
                        data: formData,
                        contentType: false,
                        processData: false,
                        success: function(e) {
                            if(e.status){
                                $.get("association.php",function(e){
                                    $(".content-main").remove();
                                    $(".content").hide();
                                    $(".content").html(`<div class="content-main">${e}</div>`).fadeIn(200);
                                });
                            }else{
                                alert(e.message);
                            }
                            console.log(e);
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            alert('File upload failed: ' + textStatus + ' - ' + errorThrown);
                        }
                    });
                }else{
                    alert("Field is required!");
                }

                $(".form-group > .form-control").change(function(){
                    $(this).css("border-color","");
                })
            })
        });
    })
});
        function editGamemodeData(id){
            $.get("edit_game_mode.php?id="+id,function(e){
                $(".content-main").remove();
                $(".content").hide();
                $(".content").html(`<div class="content-main">${e}</div>`).fadeIn(200);
            })
        }
        $('#profileBtn').click(function(){
    $('#Mymodal_profile').modal('show');
});
</script>
</body>
</html>
