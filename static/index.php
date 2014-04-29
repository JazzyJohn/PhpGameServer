<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>

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
				height: 600,
				params: { enableDebugging:"0" }
				
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

                        u.initPlugin(jQuery("#unityPlayer")[0], "/kaspi/static/builds0.0.11.unity3d");

                  });

	


});
var answer={};
function SayMyName(){
        VK.api("getProfiles", {uid:uid}, function(data) {
                
                    answer = data.response[0];
					//console.log(answer);        
                        u.getUnity().SendMessage("Player", "SetName", answer.first_name+" "+answer.last_name);
						
						
                     });
}
function SayMyUid(){
                    
                   	//console.log(answer);        
                    u.getUnity().SendMessage("Player", "SetUid", answer.uid +"");
						
					
}



						
</script>
</head>
<body>
  <div id="unityPlayer" style="height:600px;">
	<div class="missing">
		<a href="http://unity3d.com/webplayer/" title="Unity Web Player. Install now!">
			<img alt="Unity Web Player. Install now!" src="http://webplayer.unity3d.com/installation/getunity.png" width="193" height="63" />
		</a>
	</div>
</div>
</body>
</html>