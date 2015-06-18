<!--<?

require_once(dirname(dirname(__FILE__))."/conf/conf.php");
require_once(dirname(dirname(__FILE__))."/classes/vkAuth.php");

//if(!VKAuth::AUTHME()){
//
//    die ("invaders must die");
//}
ini_set('session.gc_maxlifetime', 4*3600);

// each client should remember their session id for EXACTLY 1 hour
session_set_cookie_params(4*3600);
//session_id(md5($_REQUEST["viewer_id"].SESSION_KEY));
session_start();

$_SESSION["uid"] = $_REQUEST["viewer_id"];

$version = "0.15.16.unity3d";
session_write_close();
?>
-->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

    <link rel="stylesheet" href="main.css"/>

    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>

    <script src="https://vk.com/js/api/xd_connection.js?2" type="text/javascript"></script>
    <script type="text/javascript">

        var unityObjectUrl = "http://webplayer.unity3d.com/download_webplayer-3.x/3.0/uo/UnityObject2.js";
        if (document.location.protocol == 'https:')

            unityObjectUrl = unityObjectUrl.replace("http://", "https://ssl-");
        document.write('<script type="text/javascript" src="' + unityObjectUrl + '"><\/script>');


    </script>
    <script type="text/javascript">
        var uid = <?=isset($_REQUEST["viewer_id"])?$_REQUEST["viewer_id"]:0;?>;
        var sid = '<?=session_id()?>';
        var config = {
            width: 960,
            height: 720,
            params: { enableDebugging:"0",
                backgroundcolor: "051823",
                bordercolor: "ffffff",
                textcolor: "ffffff",


                logoimage: "./logo1.png",
                progressbarimage: "./progressBar.png",
                progressframeimage: "./frameImage.png" }

        };

        if(typeof console === "undefined") {
            console = {
                log: function() { },
                debug: function() { }

            };
        }
        var u = new UnityObject2(config);
        var  socialSteps ={
            group:0,
            friends:0,
            bookmarks:0,
            invite:0,
            uid:uid,
            alldone:0
        }
        $( document ).ready(function() {

            $("#invite").click(function(){
                InviteFriend();
            });
            $("#bookmarks").click(function(){
                VK.callMethod('showSettingsBox', 256);
            });




            VK.init(function() {
                <?
                if($_REQUEST["viewer_id"]==305915){?>
                u.initPlugin(jQuery("#unityPlayer")[0], "/static/builds<?=$version;?>");
                       <?
              }else{?>
                u.initPlugin(jQuery("#unityPlayer")[0], "/static/builds<?=$version;?>");
                <?}
                ?>

                VK.addCallback('onOrderSuccess', function(order_id) {


                    u.getUnity().SendMessage("MainPlayer", "ReloadProfile","");

                });
                VK.addCallback('onOrderFail', function() {


                });
                VK.addCallback('onOrderCancel', function() {


                });
                VK.addCallback("onWindowBlur", onWindowBlur);
                function onWindowBlur() {
                    document.getElementById('unityPlayer').style.visibility = 'hidden';
                    document.getElementById('resumebtn').style.visibility = 'visible';
                }
                VK.addCallback("onWindowFocus", onWindowFocus);
                function onWindowFocus() {
                    SendReward();
                    document.getElementById('unityPlayer').style.visibility = 'visible';
                    document.getElementById('resumebtn').style.visibility = 'hidden';
                }
                VK.api('groups.isMember', {gid:"78720115"},function(data){

                    if(data['response']==1){
                        SendReward();
                    }
                });
                $("#group").click(function(){
                    var intervalid = null;
                    function checkGroup(){
                        VK.api('groups.isMember', {gid:"78720115"},function(data){

                            if(data['response']==1){
                                clearInterval(intervalid);
                                SendReward();
                            }
                        });
                    }
                    intervalid =setInterval(checkGroup(), 2000)

                });
            });




        });
        var aid= null;
        function CreateAlbum()
        {
            VK.api('photos.getAlbums', {}, function(data) {



                for(var i=0; i<data['response'].length; i++)
                {

                    if(data['response'][i]['title'] == 'RED RAGE')
                    {

                        aid = data['response'][i]['aid'];
                        VKGetUploadServer();
                        return;
                    }
                }



                VK.api('photos.createAlbum', {title: 'RED RAGE', privacy: '0', comment_privacy: '0', description: 'Многопользовательский шутер про противостояние войск СССР и НАТО.'}, function(data)
                {
                    aid = data['response']['aid'];
                    VKGetUploadServer();
                });

            });
        }

        function VKGetUploadServer()
        {

            VK.api('photos.getUploadServer', {aid: aid}, function(data)
            {
               var upload_url = data['response']['upload_url'];



                u.getUnity().SendMessage("MainPlayer", "UploadURL", upload_url);
            });
        }
        function VKSaveUpload(json_str)
        {



            var my_array = JSON.parse(json_str);

            var server = my_array['server'];
            var photos_list = my_array['photos_list'];
            var aid = my_array['aid'];
            var hash = my_array['hash'];

            VK.api('photos.save', {server: server, photos_list: photos_list, aid: aid, hash: hash, caption: 'RED RAGE. Присоединяйся: http://vk.com/app4596119'}, function(data)
            {
                var unity = u.getUnity();
                unity.SendMessage("MainPlayer", "UploadComplite", "1");
            });

        }
        var message ='RED RAGE';
        function VKGiveWallServer(messageIn)
        {
            message= messageIn;
            VK.api('photos.getWallUploadServer', function(data)
            {
                upload_url = data['response']['upload_url'];


                var unity = u.getUnity();
                unity.SendMessage("MainPlayer", "UploadURLToWall", upload_url);
            });
        }


        function VKWallPhotoPost(mess){
            var data = JSON.parse(mess);


            var server = data['server'];
            var photo = data['photo'];
            var hash = data['hash'];

            VK.api('photos.saveWallPhoto', {server: server, photo: photo, hash: hash}, function(data)
            {


                VK.api("wall.post", {message:message,attachments: data["response"][0]["id"]+",http://vk.com/app4596119"}, function(data) {
                    console.log(data);

                });


            });
        }
        var answer={};
        function AskSID(){
            u.getUnity().SendMessage("MainPalyer","SetSid",sid);
        }
        function SayMyName(){
            VK.api("getProfiles", {uid:uid,fields:"first_name,last_name,uid,photo_medium"}, function(data) {

                answer = data.response[0];


                u.getUnity().SendMessage("MainPlayer", "SetName", answer.first_name+" "+answer.last_name);
                u.getUnity().SendMessage("MainPlayer", "AskAvatar", answer.photo_medium);


            });
        }
        function SayMyUid(){

            //console.log(answer);
            u.getUnity().SendMessage("MainPlayer", "SetUid", answer.uid +"");
            VK.api("friends.getAppUsers", {}, function(data) {




                u.getUnity().SendMessage("MainPlayer", "addFriendInfoList", data.response.join(","));


            });



        }

        function GetUsers(uids){
            VK.api("users.get", {user_ids:uids,fields:"first_name,last_name,uid,photo_medium"}, function(data) {

                answer = data.response;

                var result = [];

                for(var i=0; i < answer.length;i++){
                    var tar = [];
                    tar[0] = answer[i].uid;
                    tar[1] = answer[i].first_name + " "+answer[i].last_name;
                    tar[2] = answer[i].photo_medium;
                    result [result.length]=  tar.join(";");
                }

                u.getUnity().SendMessage("MainPlayer", "ReturnUsers",  result.join(","));
            });
        }
        function getRandomArbitary(min, max)
        {
            return Math.round( Math.random() * (max - min) + min);
        }
        var achivment_images =
            [
                "-78720115_344278770",
                "-78720115_344278771",
                "-78720115_344278772",
                "-78720115_344278773",
                "-78720115_344278774",
                "-78720115_344278776",
                "-78720115_344278778",
                "-78720115_344278779",
                "-78720115_344278780",
                "-78720115_344278782",
                "-78720115_344278785",
                "-78720115_344278787",
                "-78720115_344278788",
                "-78720115_344278789",
                "-78720115_344278790"

            ];
        function AchivmenUnlock(mess){

            VK.api("wall.post", {message:mess,attachments:"photo"+achivment_images[getRandomArbitary(0,achivment_images.length)]+",http://vk.com/app4596119"}, function(data) {
                console.log(data);

            });
        }
        function WallPost(mess){
               VK.api("wall.post", {message:mess,attachments:"photo"+achivment_images[getRandomArbitary(0,achivment_images.length)]+",http://vk.com/app4596119"}, function(data) {
                console.log(data);

            });
        }

        function ItemBuy(item){
            var params = {
                type: 'item',
                item: item
            };

            VK.callMethod('showOrderBox', params);
        }
        function  SendReward(){
            if(socialSteps.alldone==1){
                return;
            }
            VK.api('groups.isMember', {gid:"78720115"},function(data){
                if(data["response"]==1){
                    socialSteps.group =1;
                    $("#group").addClass("done_4");
                }

                VK.api("getUserSettings",{},function(data){
                   if(data["response"] & 256){
                       socialSteps.bookmarks =1;
                       $("#bookmarks").addClass("done_3");
                   }
                    VK.api("friends.getAppUsers",{},function(data){
                        console.log(data);
                        if(data["response"].length>0){
                            socialSteps.friends = 1;
                        }
                        $.post( "/socialPrize", socialSteps,function(data){

                            if(data == 1){
                                socialSteps.alldone= 1;

                                $("#invite").addClass("done_2");
                                $("#prize").addClass("done_5");
                                u.getUnity().SendMessage("MainPlayer", "ReloadProfile","");
                            }
                        } );
                    });

                });

            });
        }
        function InviteFriend() {
            VK.callMethod('showInviteBox');
             socialSteps.invite =1;

        }
        var social_images =
            [
                {image:"-78720115_367001941",
                    text:"Что мы говорим НАТО? Ай маст брейк ю! http://vk.com/app4596119 #red_rage"},
                {image:"-78720115_367001937",
                    text:"Дошли до Берлина - дойдем и до Вашингтона! http://vk.com/app4596119 #red_rage"},
                {image:"-78720115_367001947",
                    text:"Да взойдет красное солнце! http://vk.com/app4596119 #red_rage"},
                {image:"-78720115_367001951",
                    text:"Дай НАТО достойный отпор! http://vk.com/app4596119 #red_rage"}





            ];
        function SocialMessagePost(mess){
            var i=getRandomArbitary(0,social_images.length);
            VK.api("wall.post", {message:social_images[i].text,attachments:"photo"+social_images[i].image+",http://vk.com/app4596119"}, function(data) {

                if (data.response) {
                    u.getUnity().SendMessage("MainPlayer", "SocialReward_OpenWall","");
                }
            });
        }

    </script>
</head>
<body>

<div class="container">
    <div class="panel panel-top">
        <div class="top-panel">
            <a href="#" class="top-panel-point top-logo"></a>

            <a href="#"   class="top-panel-point top-install done"></a>
            <a href="#" id="invite"class="top-panel-point top-add "></a>
            <a  href="#"  id="bookmarks" class="top-panel-point top-group"></a>
            <a href="https://vk.com/redrage3d"  id="group"   target="_blank" class="top-panel-point top-invate"></a>

            <div id="prize" class="top-panel-point top-prize"></div>
        </div>
    </div>
</div>

<div id="unityPlayer" style="width:960px;height:720px; margin-left:20px;">
    
<div class="unityError">


    <h2 style="width: 100%; text-align: center; margin-top: 40px;">ВНИМАНИЕ! ПЛАГИН UNITY НЕ УСТАНОВЛЕН</h2>

        <div style="width: 100%; text-align: center; font-size: 14px;">Для установки плагина, нажмите на кнопку ниже<br><br>

            <a href="http://unity3d.com/webplayer/" title="Unity Web Player. Install now!" target="_blank" style="font-size: 11px; font-family: tahoma;">
                <div class="browser-img" style="width: 193px; height: 63px; background: url(http://webplayer.unity3d.com/installation/getunity.png) no-repeat;"></div>
            </a>

        </div>

    <h2>Солдат!<br>Ты используешь Google Chrome</h2>

    <div class="text_block">

        <h3>Что бы его починить:</h3>

    <p style="line-height: 2;">
    1. Перейди по адрессу: <strong>chrome://flags/#enable-npapi</strong><br>
    2. Нажми "Включить NPAPI" (обычно выделено желтым цветом)<br>
    3. Внизу появится кнопка "Перезапустить" - нажми на нее и все заработает
    </p>
    </div>


    <h2 style="width: 100%; text-align: center; margin-top: 20px;">Или просто используй другой браузер</h2>


    <div class="browsers">
        <div class="browser-box">
            <a href="https://browser.yandex.com" target="_blank" style="font-size: 11px; font-family: tahoma;">
                <div class="browser-img" style="width: 77px; background: url(img/yandex.png) no-repeat;"></div>
                <div class="browser-link"><u>Yandex.Браузер</u><br></div>
            </a>
            <span style="text-decoration: none;">(рекомендуется)</span>
        </div>

        <div class="browser-box">
            <a href="https://www.mozilla.org/ru/firefox/desktop/" target="_blank" style="font-size: 11px; font-family: tahoma;">
                <div class="browser-img" style="width: 78px; background: url(img/firefox.png) no-repeat;"></div>
                <div class="browser-link"><u>Mozilla Firefox</u><br></div>
            </a>
            <br>
        </div>

        <div class="browser-box">
            <a href="http://www.opera.com/ru/computer/windows" target="_blank" style="font-size: 11px; font-family: tahoma;">
                <div class="browser-img" style="width: 72px; background: url(img/opera.png) no-repeat;"></div>
                <div class="browser-link"><u>Opera</u><br></div>
            </a>
            <br>
        </div>

    </div>


</div>



</div>
<div id="resumebtn" style="height:720px; position: absolute; top:150px;visibility: hidden;">
    <img src="/static/nazhimai.png"  width="960px"  height="720px"  />
</div>

<video width="1000" height="100" autoplay="autoplay" loop="true">
    <source src="video/promo_0.1.mp4" type="video/mp4">
</video>



    <!--Twitter widget start-->

    <div class="twitter-container">

        <style type="text/css" id="twitterStyle">

            #twitterStyled .stream {
                background-color: #1b1b1b;

            }

            #twitterStyled .twitter-container {
                width: 900px;
            }

            .footer {
                display: none;
            }

            #twitterStyled .tweet {
                padding: 6px 10px 6px 65px;
                overflow: hidden;
                height: 48px;
            }

            #twitterStyled .tweet:last-child {
                border-bottom: 1px none;
            }

            #twitterStyled .p-name {
                color: darkgrey;
                opacity: 0.3;
            }

            #twitterStyled .p-nickname {
                color: darkgrey;
                opacity: 0.3;
            }

        </style>

        <a class="twitter-timeline"  href="https://twitter.com/RedRage3d"  data-widget-id="584051127311794177" data-tweet-limit="2" data-chrome="noheader nofooter" ></a>
    </div>
    <!--Twitter widget end-->


<script type="text/javascript">

    $(document).ready(function() {

        //twitter
        $('a').css({"font-size": "11px", "font-family": "tahoma"});
        !function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+"://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");
        $(function () {
            var $cont = $(".twitter-container"),
                    prd = setInterval(function () {
                        if ($cont.find("> iframe").contents().find(".twitter-timeline").length > 0) {
                            var $body = $cont.find("> iframe").contents().find("body");
                            clearInterval(prd)
                            $body.attr("id", "twitterStyled")
                                    .append($("#twitterStyle"));
                            $('#twitter-widget-0').css("width", "1000px");

                        }
                    }, 100);
        });
        //VK.Widgets.Like('vk_like', {width: 500, pageTitle: 'Статья номер 321', pageDescription: 'Описание статьи номер 321'}, 321);
    });
</script>
</body>
</html>