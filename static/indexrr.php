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
var achivment_images =
    [
    "69575283_325521498"
    ];

</script>
<script type="text/javascript">
var uid = <?=isset($_REQUEST["viewer_id"])?$_REQUEST["viewer_id"]:0;?>;
  	var config = {
				width: 960,
				height: 700,
				params: { enableDebugging:"0",
                                backgroundcolor: "ffffff",
                                bordercolor: "ffffff",
				textcolor: "ffffff",
			

                                logoimage: "./logo.png",
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


					VK.init(function() {

                     u.initPlugin(jQuery("#unityPlayer")[0], "/static/builds0.3.5.unity3d?rc=3");
                        VK.addCallback('onOrderSuccess', function(order_id) {
                           console.log(  u.getUnity());

                            u.getUnity().SendMessage("MainPlayer", "ReloadProfile","");
                            console.log("sendingmessage");
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
                            document.getElementById('unityPlayer').style.visibility = 'visible';
                            document.getElementById('resumebtn').style.visibility = 'hidden';
                        }
               });

	


});
var answer={};
function SayMyName(){
        VK.api("getProfiles", {uid:uid,fields:"first_name,last_name,uid,photo_medium"}, function(data) {
                
                    answer = data.response[0];
					console.log(answer);
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

function getRandomArbitary(min, max)
{
    return Math.random() * (max - min) + min;
}

function AchivmenUnlock(mess){
   
 VK.api("wall.post", {message:"Достижение открыто: " +mess,attachments:"photo-"+achivment_images[getRandomArbitary(0,achivment_images.length)]+",http://vk.com/app4596119_305915"}, function(data) {
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



</script>
</head>
<body >

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