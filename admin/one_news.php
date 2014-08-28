<?php
/**
 * Created by PhpStorm.
 * User: 804129
 * Date: 03.06.14
 * Time: 19:18
 */

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
    "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
    <title>!DOCTYPE</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link rel="stylesheet" href="//code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css">

    <script src="//code.jquery.com/jquery-1.10.2.js"></script>
    <script src="//code.jquery.com/ui/1.10.4/jquery-ui.js"></script>
    <script>
        $(function() {
            $( "#slider" ).slider({
                value:12,
                min: 12,
                max: 50,
                step: 1,
                slide: function( event, ui ) {
                    $( "#fontvalue" ).html(  ui.value+"px" );
                    $("#maintext").css( "font-size",  ui.value +"px");
                }
            });

            $( "#maintext" ).draggable({ containment: "#maindiv"});
            $("#input_text").change(function(e){
             //   console.log($(this).val());
              $("#maintext").html( $(this).val());
            });
            $("#fontcolor").change(function(e){
                //console.log($(this).val());
                $("#maintext").css( "color","#"+ $(this).val());
            });
            $( "button" )
                .button()
                .click(function( event ) {
                    event.preventDefault();
                    var obj ={};
                    obj.text =  $("#maintext").html();
                    obj.fontsize =  $("#maintext").css( "font-size");
                    obj.color =     $("#fontcolor").val();
                    obj.id=     $("#newid").html();
                    var thisPos = $("#maintext").position();
                    var parentPos = $("#maintext").parent().position();
                    console.log( $("#maintext").parent().size());
                    var x = thisPos.left - parentPos.left;
                    var y = thisPos.top - parentPos.top;
                    obj.textX =x/ $("#maintext").parent().width()*100;
                    obj.textY =y/ $("#maintext").parent().height()*100;
                    $.ajax({
                        url:'/savenews',
                        data:obj,
                        method:"POST"



                    })
                });
        });
    </script>
</head>
<body>
<a href="/listofnews">К списку</a></br>
Текст: <textarea id="input_text"><?=$new["text"]?></textarea></br>
Font Size:<span id="fontvalue"></span> <div id="slider" style="width: 50%" ></div>
Font Color:<input id="fontcolor" value="<?=$new["color"]==""?"000000":$new["color"]?>"/><br/>
<br/>
<br/>
<div id="maindiv" >
    <img  id="maindiv" src="<?=$new["image"]?>"/>
    <span id="maintext" style="display:block;width: 200px;" ><?=$new["text"]?></span>

</div>
<div id="newid" style="display: none;"><?=$new["id"]?></div>
<button>Сохранить</button>
</body>
<html>