<?php
include "../conn.php";
include "head.php";

// Fetch user info from session
$user_id = $_SESSION['session_user_id'] ?? null;
$user_type = $_SESSION['session_type'] ?? null;

if($user_id){
    $user_sql = $con->prepare("SELECT first_name FROM tbl_profile WHERE user_id = ?");
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
            background-color: #f4f4f4; /* Light gray background */
            color: #333; /* Dark text for readability */
        }

        /* Sidebar Styles */
        .sidebar {
            width: 250px;
            background-color: #272c33; /* Black sidebar */
            color: #fff; /* White text */
            transition: transform 0.3s ease;
            transform: translateX(0);
            position: fixed;
            height: 100%;
            padding: 15px;
            overflow-y: auto;
            z-index: 10;
        }

        .sidebar.hide {
            transform: translateX(-250px); /* Hide completely outside view */
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
        /* Main Content Styles */
        .main-content {
            flex-grow: 1;
            margin-left: 250px; /* Same as sidebar width */
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
            background-color: #555; /* Dark gray for hover effect */
        }

        .header {
            font-size: 24px;
            margin-bottom: 20px;
            color: #333;
            margin-left: 50px;
        }

        .content {
            background-color: #fff; /* White content background */
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
    </style>
</head>
<body>
<?php include "modals.php" ?>
    <div class="sidebar" id="sidebar">
        <h3>Dashboard Menu</h3>
        <ul class="nav-ul">
            <li data-href="home.php"><span class='glyphicon glyphicon-home'></span> <a href="#">Home</a></li>
            <li data-href="association.php" ><span class='glyphicon glyphicon-user'></span> <a href="#" >Colleges</a></li>
            <li data-href="tournament.php" > <span class='glyphicon glyphicon-random'></span> <a href="#">Tournament</a></li>
            <!-- <li data-href="generate.php" ><span class='glyphicon glyphicon-book'></span> <a href="#" >Reports</a></li> -->
            <li data-href="logout.php"><span class='glyphicon glyphicon-log-out'></span> <a href="#">Logout</a></li>
        </ul>
    </div>

    <div class="main-content" id="main-content">
        <button class="toggle-btn" id="toggle-btn">☰</button>
        <div class="header_event">

        <!-- Profile Button -->
    <button id="profileBtn" class="btn btn-profile">
    <span class="glyphicon glyphicon-user"></span> <span style="font-weight: bold;">
        <?php echo htmlspecialchars($user['first_name'] ?? $user_type_name); ?> 
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
                        $sql = $con->query("SELECT ev_id,ev_name FROM tbl_event");
                        if(mysqli_num_rows($sql) > 0){
                            while($row=mysqli_fetch_assoc($sql)){
                    ?>
                        <li><a href="index.php?e_id=<?php echo $row['ev_id'] ?>"><?php echo $row['ev_name'] ?> <?php echo $span_data = ($row['ev_id']==$event_details['ev_id'])? "<span class='glyphicon glyphicon-pushpin'></span>" :" " ?></a></li>
                        <li role="separator" class="divider"></li>
                        <?php }
                        }else{
                            echo "<script>$('#Mymodal_add_event').modal()</script>";
                        } ?>
                </ul>
                </div>
            </label>
        </div>
        <div class="content">
        </div>
    </div>
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

    $("#event_details").click(function(){
        $("#Mymodal_update_event").modal();
    })
    function getPage(nav,access){
        let link = nav.attr("data-href");
        if(access){
                localStorage.setItem('hreflink',link);
                $(".nav-ul > li").removeClass("active");
                nav.addClass("active");
                if(link == "logout.php"){
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

            
            $("#Add_sport_form").submit(function(e){
                e.preventDefault();
                let cont = true;
                $("#Add_sport_form > .form-group > .form-control").each(function(){
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
        });
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
});
$('#profileBtn').click(function(){
    $('#Mymodal_profile').modal('show');
});
</script>
</body>
</html>
