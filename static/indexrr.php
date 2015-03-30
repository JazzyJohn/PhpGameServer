﻿<!--<?

require_once(dirname(dirname(__FILE__))."/conf/conf.php");
require_once(dirname(dirname(__FILE__))."/classes/vkAuth.php");

//if(!VKAuth::AUTHME()){
//
//    die ("invaders must die");
//}
session_start();
$_SESSION["uid"] = $_REQUEST["viewer_id"];

$version = "0.11.8.unity3d";
session_write_close();
?>
-->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <style>
        body {
            background-color: #051823; /* ???? ???? ???-???????? */
        }

        .container {
            display: block;
            margin-right: auto;
            margin-left: auto;
        }

        .panel {
            display: block;
            margin-right: auto;
            margin-left: auto;
            width: 1000px;
        }

        .top-panel {
            background-image: url("/static/img/top-panel.png");
            width: 1000px;
            height: 71px;
            position: relative;
            margin: 4px 0;
        }

        .top-panel-point {
            position: absolute;
            top: 7px;
            height: 43px;
            width: 70px;
            padding: 14px 32px 0 55px;
            color: #58707f;
            font-size: 12px;
            font-family: "Myriad Pro";
            text-decoration: none;
            display: block;
            z-index: 2;
        }

        .top-logo {
            left: 7px;
            width: 137px !important;
        }

        .top-add {
            left: 388px;
        }

        .top-install {
            left: 231px;
        }

        .top-group {
            left: 545px;
        }

        .top-invate {
            left: 702px;
        }

        .top-prize {
            left: 859px;
            width: 47px !important;
        }

        .top-panel-point:hover {
            color: #fcfcfc;
            text-decoration: underline;
        }

        .done {
            background-image: url("/static/img/top-active.png");
            padding-left: 83px;
            left: 203px;
            color: #ffffff !important;
        }

        .done_2{
            background-image: url("/static/img/top-active.png");
            padding-left: 83px;
            left: 360px;
            color: #ffffff !important;
        }

        .done_3{
            background-image: url("/static/img/top-active.png");
            padding-left: 83px;
            left: 517px;
            color: #ffffff !important;
        }

        .done_4{
            background-image: url("/static/img/top-active.png");
            padding-left: 83px;
            left: 674px;
            color: #ffffff !important;
        }

        .done_5 {
            padding-left: 75px !important;
            background-image: url("/static/img/gold-active.png");
            color: #ffffff !important;
            left: 839px !important;
        }

    </style>
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
            width: 1000,
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
                    $("#group").addClass("done_3");
                }

                VK.api("getUserSettings",{},function(data){
                   if(data["response"] & 256){
                       socialSteps.bookmarks =1;
                       $("#bookmarks").addClass("done_2");
                   }
                    VK.api("friends.getAppUsers",{},function(data){
                        console.log(data);
                        if(data["response"].length>0){
                            socialSteps.friends = 1;
                        }
                        $.post( "/socialPrize", socialSteps,function(data){

                            if(data == 1){
                                socialSteps.alldone= 1;

                                $("#invite").addClass("done_4");
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


    </script>
</head>
<body>

<div class="container">
<div class="panel panel-top">
    <div class="top-panel">
        <a href="#" class="top-panel-point top-logo"></a>
        <a href="#"   class="top-panel-point top-install top-active done">Установить приложение</a>
        <a href="#" id="bookmarks" class="top-panel-point top-add">Добавить в закладки</a>
        <a href="https://vk.com/redrage3d"  id="group" target="_blank" class="top-panel-point top-group">Вступить в группу</a>
        <a href="#" id="invite" class="top-panel-point top-invate">Пригласить друга</a>
        <div id="prize" class="top-panel-point top-prize">Награда 200</div>
    </div>
</div>
</div>

<div id="unityPlayer" style="height:720px;">
    <div class="missing">
        <a href="http://unity3d.com/webplayer/" title="Unity Web Player. Install now!">
            <img alt="Unity Web Player. Install now!" src="http://webplayer.unity3d.com/installation/getunity.png" width="193" height="63" />
        </a>
    </div>


</div>
<div id="resumebtn" style="height:720px; position: absolute; top:150px;visibility: hidden;">
    <img src="/static/nazhimai.png"  width="960px"  height="720px"  />
</div>
<a style="border: 0; margin: 0; padding: 0;" href="//vk.com/topic-78720115_30966102" target="_blank"><img alt="Сообщить об ошибке" src="img/botton_menu.jpg" width="1000px" height="100px"/></a>
</body>
</html>