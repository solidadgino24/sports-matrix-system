<div class="main">
    <div class="head">
        <h2>Add Sports</h2>
        <button class='btn btn-secondary btn_bck'>Back</button>
    </div>
    <div class="body">
        <form action="#" id="add_sport_form" class="col-md-6 col-md-offset-3">
            <h3>Sport</h3>
            <div class="page1">
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" id="name" class="form-control" >
                </div>
                <div class="form-group">
                    <label for="img">Image</label>
                    <input type="file" id="img" class="form-control" accept=".img,.png,.jpeg,.jpg">
                    <img id="img_preview" src="" alt="Image Preview" style="display:none; margin-top:10px; max-width:100%; height:auto; border:1px solid #ccc; border-radius:6px;">
                </div>
                <div class="form-group">
                    <label for="rules">General Rules (PDF, images)</label>
                    <input type="file" id="rules" class="form-control">
                </div>
            </div>
            <div class="page2" style="display:none;">
                <div class="game_modes">
                    <div class="game_mode">
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
                </div>
                <span class="btn btn-info btn-sm" id="add_game_modes"><i class="glyphicon glyphicon-plus"></i> Add new</span>
            </div>
            <div class="form-navigate">
                <div class="page1_nav">
                    <span class='btn btn-success pull-right'>Next</span>
                </div>
                <div class="page2_nav" style="display:none;">
                    <button class='btn btn-success pull-right'>Save</button>
                    <span class='btn btn-secondary pull-right'>Back</span>
                </div>
            </div>
        </form>
    </div>
    <div class="clearfix"></div>
</div>

<style>
.head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 2px solid #272c33;
    padding: 10px 0;
    margin-bottom: 15px;
}
.head > h2 {
    margin: 0;
    font-weight: bold;
}
.head > button {
    border: none;
    background-color: seagreen;
    color: #fff;
    font-weight: bold;
    padding: 8px 14px;
    border-radius: 6px;
    cursor: pointer;
    transition: background 0.2s ease;
}
.head > button:hover {
    background-color: mediumseagreen;
}

form h3 {
    font-weight: 600;
    margin-bottom: 15px;
    border-left: 4px solid coral;
    padding-left: 8px;
}

.form-group label {
    font-weight: 500;
    margin-bottom: 5px;
    display: block;
}
.form-control {
    border: 1px solid #ccc;
    border-radius: 6px;
    padding: 8px 10px;
    width: 100%;
    transition: border 0.2s ease;
}
.form-control:focus {
    border-color: coral;
    outline: none;
    box-shadow: 0 0 4px rgba(255, 127, 80, 0.4);
}

.game_mode {
    border: 1px solid #ddd;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 15px;
    background: #fafafa;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    position: relative;
}
.game_mode > .remove {
    position: absolute;
    right: 12px;
    top: 12px;
    cursor: pointer;
    border: none;
    background: crimson;
    color: #fff;
    font-size: 14px;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    line-height: 20px;
    text-align: center;
    transition: background 0.2s ease;
}
.game_mode > .remove:hover {
    background: darkred;
}

#add_game_modes {
    margin-top: 10px;
    display: inline-block;
    background: coral;
    color: #fff;
    border-radius: 5px;
    padding: 6px 12px;
    cursor: pointer;
    transition: background 0.2s ease;
}
#add_game_modes:hover {
    background: #e06b4d;
}

.form-navigate {
    margin-top: 20px;
    border-top: 1px solid #eee;
    padding-top: 15px;
}
.form-navigate .btn {
    min-width: 100px;
    margin-left: 10px;
}
</style>

<script>
$(document).ready(function(){

    // Back button
    $(".btn_bck").click(function(){
        $.get("sports.php",function(e){
            $(".content-main").remove();
            $(".content").hide();
            $(".content").html(`<div class="content-main">${e}</div>`).fadeIn(200);
        })
    });

    // Image preview
    $("#img").on("change", function() {
        const file = this.files[0];
        const preview = $("#img_preview");

        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.attr("src", e.target.result);
                preview.show();
            }
            reader.readAsDataURL(file);
        } else {
            preview.hide();
            preview.attr("src", "");
        }
    });

    // Scoring selection toggle
    $("#scoring").on("change", function() {
        const val = $(this).val();
        if(val == "1"){
            $(".point_opt").show();
            $(".set_opt").hide();
        } else if(val == "2"){
            $(".set_opt").show();
            $(".point_opt").hide();
        } else {
            $(".point_opt, .set_opt").hide();
        }
    });

    // Page navigation
    $(".page1_nav .btn-success").click(function(){
        $(".page1").hide();
        $(".page1_nav").hide();
        $(".page2").show();
        $(".page2_nav").show();
    });

    $(".page2_nav .btn-secondary").click(function(){
        $(".page2").hide();
        $(".page2_nav").hide();
        $(".page1").show();
        $(".page1_nav").show();
    });

    // Add new game mode
    $("#add_game_modes").click(function(){
        const newMode = $(".game_mode:first").clone();
        newMode.find("input").val("");
        newMode.find("select").val("");
        newMode.find(".point_opt, .set_opt").hide();
        newMode.append('<button type="button" class="remove">&times;</button>');
        $(".game_modes").append(newMode);
    });

    // Remove game mode
    $(document).on("click", ".game_mode .remove", function(){
        $(this).closest(".game_mode").remove();
    });

});
</script>
