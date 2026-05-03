function aPhis(pkmn) {

if(pkmn == 1){
    $("#pMy").animate({
                "marginLeft": "+110%",
                "marginTop": "-50%"
        }, 700);
		 $("#pMy").animate({
                "marginLeft": "0%",
                "marginTop": "0%"
        }, 700);
}else{
$("#pVs").css("position", "absolute");
    $("#pVs").animate({
                "marginLeft": "-25%",
                "marginTop": "+15%"
        }, 700);
		 $("#pVs").animate({
                "marginLeft": "0%",
                "marginTop": "0%"
        }, 700, function () {
		$("#pVs").css("position", "relative");
		});
}
}

function aSpec(pkmn,type) {
if(pkmn == 1){
$("#moves_one").css("display", "block");
$("#moves_one").css("position", "absolute");
$("#moves_one").html("<img src=/img/moves/electroball.png>");
if(type == "Ice"){
$("#moves_one").html("<img src=/img/moves/icicle.png>");
}
if(type == "Grass"){
$("#moves_one").html("<img src=/img/moves/energyball.png>");
}
if(type == "Electric"){
$("#moves_one").html("<img src=/img/moves/electroball.png>");
}
if(type == "Fire"){
$("#moves_one").html("<img src=/img/moves/fireball.png>");
}
if(type == "Poison"){
$("#moves_one").html("<img src=/img/moves/poisonwisp.png>");
}
if(type == "Rock"){
$("#moves_one").html("<img src=/img/moves/rock2.png>");
}
if(type == "Water"){
$("#moves_one").html("<img src=/img/moves/waterwisp.png>");
}
    $("#moves_one").animate({
                "marginLeft": "+33%",
                "marginTop": "-16%"
        }, 700, function () {
		$("#moves_one").css("display", "none");
		});
		 $("#moves_one").animate({
                "marginLeft": "0%",
                "marginTop": "0%"
        }, 700);
}else{
$("#moves_two").css("display", "block");
$("#moves_two").css("position", "absolute");
$("#moves_two").html("<img src=/img/moves/electroball.png>");
if(type == "Ice"){
$("#moves_two").html("<img src=/img/moves/icicle.png>");
}
if(type == "Grass"){
$("#moves_two").html("<img src=/img/moves/energyball.png>");
}
if(type == "Electric"){
$("#moves_two").html("<img src=/img/moves/electroball.png>");
}
if(type == "Fire"){
$("#moves_two").html("<img src=/img/moves/fireball.png>");
}
if(type == "Poison"){
$("#moves_two").html("<img src=/img/moves/poisonwisp.png>");
}
if(type == "Rock"){
$("#moves_two").html("<img src=/img/moves/rock2.png>");
}
if(type == "Water"){
$("#moves_two").html("<img src=/img/moves/waterwisp.png>");
}

    $("#moves_two").animate({
                "marginLeft": "-12%",
                "marginTop": "+7%"
        }, 700, function () {
		$("#moves_two").css("display", "none");
		});
		 $("#moves_two").animate({
                "marginLeft": "0%",
                "marginTop": "0%"
        }, 700);
}
}

function aStat(pkmn) {
if(pkmn == 1){
    $("#pMy").animate({
                "marginLeft": "+40%",
        }, 700);
		  $("#pMy").animate({
                "marginLeft": "-60%"
        }, 700);
		 $("#pMy").animate({
                "marginLeft": "0%",
                "marginRight": "0%"
        }, 700);
}else{
    $("#pVs").animate({
                "marginLeft": "+40%",
        }, 700);
		  $("#pVs").animate({
                "marginLeft": "-60%"
        }, 700);
		 $("#pVs").animate({
                "marginLeft": "0%",
                "marginRight": "0%"
        }, 700);
}
}

function hpMin(count,pkm) {

}