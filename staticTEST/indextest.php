<!--<?

require_once(dirname(dirname(__FILE__))."/conf/conf.php");
require_once(dirname(dirname(__FILE__))."/classes/vkAuth.php");


session_start();
$_SESSION["uid"] = $_REQUEST["viewer_id"];


session_write_close();
?>
-->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <style>
        body{
            background-color: #ffffff; /* ???? ???? ???-???????? */
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
            width: 960,
            height: 700,
            params: { enableDebugging:"0",
                backgroundcolor: "FFAE00",
                bordercolor: "FFAE00",
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
        $( document ).ready(function() {



                u.initPlugin(jQuery("#unityPlayer")[0], "/staticTEST/web_builde_RR.unity3d");





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



                VK.api('photos.createAlbum', {title: 'RED RAGE', privacy: '0', comment_privacy: '0', description: '��������������������� ����� ��� �������������� ����� ���� � ����.'}, function(data)
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

            VK.api('photos.save', {server: server, photos_list: photos_list, aid: aid, hash: hash, caption: 'RED RAGE. �������������: http://vk.com/app4596119'}, function(data)
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

                u.getUnity().SendMessage("MainPlayer", "SetName", "name");
                u.getUnity().SendMessage("MainPlayer", "AskAvatar", "photo_medium");

        }
        function SayMyUid(){

            //console.log(answer);
            u.getUnity().SendMessage("MainPlayer", "SetUid", "EDITOR1");




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

        function InviteFriend() {
            VK.callMethod('showInviteBox');

        }


    </script>
</head>
<body style="background-color: #FFAE00" >
<div>

    <a style="border: 0; margin: 0; padding: 0;" href="//vk.com/topic-78720115_30966102" target="_blank"><img alt="�������� �� ������" src="btn/oshibki.png" width="320" height="80"/></a>
    <a style="border: 0; margin: 0; padding: 0;" href='#' onclick="InviteFriend();"><img alt="���������� �����" src="btn/pozvat_druzei.png" width="320" height="80"/></a>
    <a style="border: 0; margin: 0; padding: 0;" href="//vk.com/redrage3D" target="_blank"><img alt="������ ����" src="btn/V_gruppu.png" width="320" height="80"/></a>

</div>

<div id="unityPlayer" style="height:700px;">
    <div class="missing">
        <a href="http://unity3d.com/webplayer/" title="Unity Web Player. Install now!">
            <img alt="Unity Web Player. Install now!" src="http://webplayer.unity3d.com/installation/getunity.png" width="193" height="63" />
        </a>
    </div>


</div>
<div id="resumebtn" style="height:700px; position: absolute; top:150px;visibility: hidden;">
    <img src="/static/nazhimai.png"  width="960px"  height="700px"  />
</div>

</body>
</html>