<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
  <style>
   body{
    background-color: #ffffff; /* Цвет фона веб-страницы */
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
  	var config = {
				width: 960,
				height: 700,
				params: { enableDebugging:"0",
                                backgroundcolor: "ffffff",
                                bordercolor: "ffffff",
				textcolor: "ffffff",
			

                                logoimage: "./LogoNew2.png",
                                progressbarimage: "./BarNew.png",
                                progressframeimage: "./FrameNew.png" }
				
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

                        u.initPlugin(jQuery("#unityPlayer")[0], "/kaspi/static/builds0.0.30.unity3d?hotfix");

                  });

	


});
var answer={};
function SayMyName(){
        VK.api("getProfiles", {uid:uid}, function(data) {
                
                    answer = data.response[0];
					//console.log(answer);        
                        u.getUnity().SendMessage("MainPlayer", "SetName", answer.first_name+" "+answer.last_name);
						
						
                     });
}
function SayMyUid(){
                    
                   	//console.log(answer);        
                    u.getUnity().SendMessage("MainPlayer", "SetUid", answer.uid +"");
                    VK.api("friends.getAppUsers", {}, function(data) {

                      

                      
                        u.getUnity().SendMessage("MainPlayer", "addFriendInfoList", data.response.join(","));


                    });

						
					
}
function AchivmenUnlock(mess){
 VK.api("wall.post", {message:"Разблокировано достижение: " +mess,attachments:"photo-69575283_325521498,http://vk.com/app3925872_305915"}, function(data) {
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

VK.addCallback('onOrderSuccess', function(order_id) {
    u.getUnity().SendMessage("MainPlayer", "ReloadProfile");

});
VK.addCallback('onOrderFail', function() {


});
VK.addCallback('onOrderCancel', function() {


});
						
</script>
</head>
<body >
 <a target="_blank" href="https://vk.com/page-69575283_47003869"><img src="/kaspi/static/help.png" /></a>
  <div id="unityPlayer" style="height:700px;">
	<div class="missing">
		<a href="http://unity3d.com/webplayer/" title="Unity Web Player. Install now!">
			<img alt="Unity Web Player. Install now!" src="http://webplayer.unity3d.com/installation/getunity.png" width="193" height="63" />
		</a>
	</div>
</div>



</body>
</html>