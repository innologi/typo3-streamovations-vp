/*
 http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
*/
var SvpStarter=function(g){function h(a){console.log("SVPS | "+a)}function K(){var a=g("."+e.container);if(n.topic)t.topic=g(".topics .topic",a).not(".template").length;if(n.speaker)t.speaker=g(".speakers .speaker",a).not(".template").length}function E(a){if(L>0)if(typeof SvpPolling!=="undefined"){var b=L*1E3,c=g("#"+e.data).attr("data-hash");if(a)SvpPolling.init(c,M,b);else{s(function(){SvpPolling.init(c,M,b)});v(function(){SvpPolling.stop();p("speaker");p("topic")})}}else h(j.no_svpp,true);else h(j.svpp_off,
false)}function V(a){a=a[a.length-1];try{if(a.valid){var b=Math.round((new Date).getTime()/1E3);if(k.eventBreak)a.end!==null&&a.utcEnd<=b&&W();else if(a.utc<=b)if(a.end===null||a.utcEnd>b)X()}}catch(c){h(j.invalid_eventbreak,true)}}function W(){SvpPolling.stop();var a=g("."+e.container+" .video-player-break"),b='<div id="'+e.player+'" class="video-player"></div>';a.parent().append(b);a.remove();k.eventBreak=false;d.init();F()}function X(){G();f={onTime:[],onSeek:[],onPlay:[],onPause:[]};var a=g("."+
e.container+" #"+e.playerPI).first(),b='<div class="video-player-break"><div class="text"><div class="title">'+j.video_break_title+'</div><div class="sub">'+j.video_break_sub+"</div></div></div>";a.parent().append(b);a.remove();k.eventBreak=true;E(true)}function N(a,b){a=a[a.length-1].id;k[b]!==a&&x(a,b)}function O(a,b){var c=g("."+e.container+" ."+b+"s ."+b).last(),m=c.parent();if(c.hasClass("template")){c.removeClass("template");c.remove();m.removeClass("template")}for(var l=0;l<a.length;l++){var i=
c.clone(),o=a[l];switch(b){case "speaker":g(".speaker-avatar",i).attr("src",Y+o.photo);g(".speaker-data",i).html(o.firstname+" "+o.lastname);break;case "topic":g(".topic-title",i).html(o.title);g(".topic-description",i).html(o.description)}i.attr("data-"+b,o.id).removeClass("active");m.append(i);t[b].length++}}function Z(a){var b={};for(var c in a)if(a.hasOwnProperty(c))b[a[c].streamfileId]=parseInt(c,10);return b}function $(){var a=null;if(n.topic){var b=g("."+e.container+" .topics");b.on("click",
".topic .topic-link",function(i){i.preventDefault();d.jumpToTopic(g(this).parent(".topic").attr("data-topic"))});var c=g("#"+e.topicTimeline).first();if(c.exists()){try{a=JSON.parse(c.html())}catch(m){h(j.no_json_support,true);return false}a.length>0&&P("topic",a,true);c.html("")}b.find(".topic").each(function(i,o){i=g(o);if(H.topic[i.attr("data-topic")]===undefined){o=i.find(".topic-link");if(o[0]){var aa=o.text();i.append('<span class="topic-title">'+aa+"</span>");o.remove()}}});h(j.events_topic_init,
false)}if(n.speaker){b=g("#"+e.speakerTimeline).first();if(b.exists()){a=null;try{a=JSON.parse(b.html())}catch(l){h(j.no_json_support,true);return false}a.length>0&&P("speaker",a,false);b.html("")}h(j.events_speaker_init,false)}}function P(a,b,c){for(var m=0;m<b.length;m++){var l=b[m],i=m+1;l.start=Math.floor(l.relativeTime/1E3);l.end=i<b.length&&l.streamfileId===b[i].streamfileId?Math.floor(b[i].relativeTime/1E3):Number.MAX_SAFE_INTEGER;l.playlist=I(l);if(c)H[a][l.id]={playlist:l.playlist,time:l.start};
y(z.onTime(l,a));w(z.onSeek(l,a))}w(z.onSeekFinal(a))}function ba(){d.player.addEventListener("timeupdate",function(){for(var a in f.onTime)f.onTime.hasOwnProperty(a)&&f.onTime[a]({position:this.currentTime})})}function Q(){ba();d.player.addEventListener("seeking",function(){h("Seeking event fired: time "+this.currentTime);for(var a in f.onSeek)f.onSeek.hasOwnProperty(a)&&f.onSeek[a]({offset:this.currentTime})})}function R(a,b){p("speaker");p("topic");d.player=document.getElementById(e.playerObj);
a!==undefined&&u(a,b);Q()}function S(a,b){p("speaker");p("topic");d.player=document.getElementById(e.playerObj);a!==undefined&&u(a,b)}function A(){d.jw=jwplayer(e.playerObj);d.jw.onReady(function(){p("speaker");p("topic");ca()})}function da(){for(var a in f.onTime)f.onTime.hasOwnProperty(a)&&f.onTime[a]()}function ca(){var a=null;for(a in f.onTime)f.onTime.hasOwnProperty(a)&&d.jw.onTime(f.onTime[a]);for(a in f.onSeek)f.onSeek.hasOwnProperty(a)&&d.smv.onSeek(f.onSeek[a]);ea()}function ea(){var a=null;
for(a in f.onPlay)f.onPlay.hasOwnProperty(a)&&d.smv.onPlay(f.onPlay[a]);for(a in f.onPause)f.onPause.hasOwnProperty(a)&&d.smv.onPause(f.onPause[a]);h(j.events_re,false)}function p(a){if(n[a]){var b=g("."+e.container+" ."+a+"s");g("."+a+".active",b).removeClass("active");k[a]=0;b.trigger("SVPS:inactive-"+a)}}function x(a,b){var c=g("."+e.container+" ."+b+"s"),m=g("."+b+"[data-"+b+"="+a+"]",c);g("."+b+".active",c).removeClass("active");k[b]=a;m.first().addClass("active");c.trigger("SVPS:active-"+b,
{id:a,index:m.index()});h(j.activate+" "+b+" "+a,false)}function u(a,b){B.hasOwnProperty(b)||(B[b]={});B[b][a]=true;s(function(){if(B[b][a]){q(a,b);B[b][a]=false}})}function T(){if(typeof jwplayer==="undefined"){h(j.no_jwplayer,true);return false}jwplayer.key="###JWPLAYER_KEY###";return true}function fa(a,b){if(typeof smvplayer!=="undefined"){d.smv=smvplayer(e.player);try{d.smv.init(a,b)}catch(c){h(c,true)}r=function(){return d.smv.getCurrentPlaylistItem().streamfileId};I=function(m){return m.streamfileId};
F=function(){d.smv.whenReady(function(){d.smv.play()})};G=function(){d.smv.stop()};a=d.smv.getEngine();if(a==="hlsjs"||a==="html5")ga();else if(a==="me")ha();else a==="jw"&&ia();e.playerPI=e.smvWrapper1+e.player;return true}h(j.no_smvplayer,true);return false}function ia(){d.player=d.smv;e.playerObj=e.smvWrapper1+e.smvWrapper2+e.player;d.jw=jwplayer(e.playerObj);y=function(b){f.onTime.push(b);d.jw.onTime(b)};w=function(b){f.onSeek.push(b);d.smv.onSeek(b)};s=function(b){f.onPlay.push(b);d.smv.onPlay(b)};
v=function(b){f.onPause.push(b);d.smv.onPause(b)};var a={};a.next=d.smv.next;d.smv.next=function(){a.next();A()};a.previous=d.smv.previous;d.smv.previous=function(){a.previous();A()};a.setQualityLevel=d.smv.setQualityLevel;d.smv.setQualityLevel=function(b){a.setQualityLevel(b);A()};a.setAudioLanguage=d.smv.setAudioLanguage;d.smv.setAudioLanguage=function(b){a.setAudioLanguage(b);A()};C=function(b){var c=d.smv.getStatus();if(c!=="PLAYING"&&c!=="IDLE"){u(b.time,b.playlist);d.smv.play()}else q(b.time,
b.playlist)};q=function(b,c){if(c===r())c=null;d.smv.seek(b,c);c!==null&&A()}}function ga(){e.playerObj=e.hlsWrapper+e.player;d.player=document.getElementById(e.playerObj);d.smv.onReload(function(){R()});Q();y=function(a){f.onTime.push(a)};w=function(a){f.onSeek.push(a)};s=function(a){d.smv.onPlay(a)};v=function(a){d.smv.onPause(a)};C=function(a){var b=d.smv.getStatus().toUpperCase();if(b!=="PLAYING"&&b!=="IDLE"){u(a.time,a.playlist);d.smv.play()}else q(a.time,a.playlist)};q=function(a,b){if(b===
r())b=null;d.smv.seek(a,b);b!==null&&R(a,b)}}function ha(){e.playerObj=e.hlsWrapper+e.player;d.player=document.getElementById(e.playerObj);d.smv.onReload(function(){S()});y=function(a){f.onTime.push(a)};w=function(a){d.smv.onSeek(a)};s=function(a){d.smv.onPlay(a)};v=function(a){d.smv.onPause(a)};z.onSeek=function(a,b){return function(){var c=d.smv.getPosition();if(c>=a.start&&c<a.end&&a.playlist===r()){a.id!==k[b]&&x(a.id,b);D[b]=true}}};z.onTime=function(a,b){return function(){var c=d.smv.getPosition();
c>=a.start&&c<a.start+0.4&&a.id!==k[b]&&a.playlist===r()&&x(a.id,b)}};s(function(){if(k.pausePlayInterval===null)k.pausePlayInterval=setInterval(da,400)});v(function(){k.pausePlayInterval!==null&&clearInterval(k.pausePlayInterval);k.pausePlayInterval=null});C=function(a){var b=d.smv.getStatus().toUpperCase();if(b!=="PLAYING"&&b!=="IDLE"){u(a.time,a.playlist);d.smv.play()}else q(a.time,a.playlist)};q=function(a,b){if(b===r())b=null;d.smv.seek(a,b);b!==null&&S(a,b)}}function U(a){jwplayer(e.player).setup(a);
d.jw=jwplayer(e.player);d.player=d.jw;y=function(c){d.jw.onTime(c)};w=function(c){d.jw.onSeek(c)};s=function(c){d.jw.onPlay(c)};v=function(c){d.jw.onPause(c)};r=function(){return d.jw.getPlaylistIndex()};F=function(){d.jw.onReady(function(){d.jw.play()})};G=function(){d.jw.stop()};d.jw.onPlaylistItem(function(){p("speaker");p("topic")});C=function(c){if(d.jw.getState().toUpperCase()!=="PLAYING"){u(c.time,c.playlist);d.jw.play()}else q(c.time,c.playlist)};if(a.hasOwnProperty("playlist")){var b=Z(a.playlist);
I=function(c){return b[c.streamfileId]}}else{h(j.no_playlist,true);n.topic=false;n.speaker=false}}function ja(a){if(T(true)){U(a);q=function(b,c){if(r()!==c){d.jw.once("playlistItem",function(){d.jw.once("play",function(){d.jw.seek(b)})});d.jw.playlistItem(c)}else d.jw.seek(b)};u=function(b,c){d.jw.once("play",function(){q(b,c)})};return true}return false}function ka(a){if(T(false)){U(a);q=function(b,c){if(r()!==c){J[c]=true;d.jw.onPlaylistItem(function(){if(J[c]){d.jw.seek(b);J[c]=false}});d.jw.playlistItem(c)}else d.jw.seek(b)};
return true}return false}var Y="###SPEAKER_IMAGE_DIR###",L=parseInt("###POLLING_INTERVAL###",10),M=parseInt("###CURRENT_PAGE_ID###",10),la=parseInt("###PLAYER_TYPE###",10),n={breaks:parseInt("###MEETINGDATA_BREAKS###",10),topic:parseInt("###MEETINGDATA_TOPICS###",10),speaker:parseInt("###MEETINGDATA_SPEAKERS###",10)},H={topic:{}},t={topic:0,speaker:0},k={topic:0,speaker:0,eventBreak:false,pausePlayInterval:null},f={onTime:[],onSeek:[],onPlay:[],onPause:[]},B={},J={},D={topic:false,speaker:false},
z={onTime:function(a,b){return function(c){c.position>=a.start&&c.position<a.start+0.4&&a.id!==k[b]&&a.playlist===r()&&x(a.id,b)}},onSeek:function(a,b){return function(c){if(c.offset>=a.start&&c.offset<a.end&&a.playlist===r()){a.id!==k[b]&&x(a.id,b);D[b]=true}}},onSeekFinal:function(a){return function(){!D[a]&&k[a]!==0&&p(a);D[a]=false}}},e={player:"tx-streamovations-vp-play",playerObj:"tx-streamovations-vp-play",playerPI:"tx-streamovations-vp-play",playerContainer:"video-player-container",smvWrapper1:"smvplayer_",
smvWrapper2:"engineWrapper_",hlsWrapper:"smv_html5videotag_",html5Wrapper:"_html5videotag",data:"tx-streamovations-vp-playerdata",config:"tx-streamovations-vp-playerconfig",topicTimeline:"tx-streamovations-vp-topictimeline",speakerTimeline:"tx-streamovations-vp-speakertimeline",container:"tx-streamovations-vp"},j={no_svpp:"SVPP not loaded, polling inactive",svpp_off:"Polling disabled",no_player_data:"The player element or player data is not available",no_playlist:"Missing essential playlist data",
no_json_support:"No JSON.parse support in user agent",player_data_invalid:"Player data is invalid or in an unsupported format",invalid_player:"No supported player configured",invalid_eventbreak:"Eventbreak data is invalid or in an unsupported format",no_jwplayer:"No jwplayer loaded",no_jwplayer_key:"A jwplayer license key is required",no_smvplayer:"No smvplayer loaded",events_topic_init:"initializing topic event handlers",events_speaker_init:"initializing speaker event handlers",events_re:"Reattached event callbacks",
no_timestamp:"Topic has no registered timestamps",no_playlist_seek:"Can only seek to other playlist item during playback",activate:"Activated",video_break_title:"###VIDEO_BREAK_TITLE###",video_break_sub:"###VIDEO_BREAK_SUB###"};g.fn.exists=function(){return this.length!==0};var w=null,y=null,s=null,v=null,r=null,I=null,C=null,q=null,G=null,F=null,d={player:null,jw:null,smv:null,isLiveStream:false,init:function(){var a=g("."+e.playerContainer),b=g("#"+e.data).first();if(!a.exists()||!b.exists()){h(j.no_player_data,
true);return false}var c=null,m=null;try{c=JSON.parse(b.html().trim())}catch(l){h(j.no_json_support,true);return false}if(typeof c!=="object"){h(j.player_data_invalid,true);return false}if(c.hasOwnProperty("application"))this.isLiveStream=c.application==="rtplive";if(g("#"+e.player,a).exists())try{switch(la){case 3:var i=g("#"+e.config).first();if(i.exists())m=JSON.parse(i.html().trim());if(!fa(c,m))return false;break;case 2:if(!ja(c))return false;break;case 1:if(!ka(c))return false;break;default:h(j.invalid_player,
true);return false}s(function(){a.trigger("SVPS:play")});if(this.isLiveStream){if(n.topic||n.speaker||n.breaks){K();E(false)}}else $()}catch(o){h(o,true)}else if(this.isLiveStream&&n.breaks){k.eventBreak=true;K();E(true)}return true},processMeetingdataChange:function(a){if(n.topic){a.hasOwnProperty("topics")&&a.topics!==false&&a.topics.length>t.topic&&O(a.topics.slice(t.topic),"topic");a.hasOwnProperty("topicTimeline")&&a.topicTimeline!==false&&a.topicTimeline.length>0&&N(a.topicTimeline,"topic")}if(n.speaker){a.hasOwnProperty("speakers")&&
a.speakers!==false&&a.speakers.length>t.speaker&&O(a.speakers.slice(t.speaker),"speaker");a.hasOwnProperty("speakerTimeline")&&a.speakerTimeline!==false&&a.speakerTimeline.length>0&&N(a.speakerTimeline,"speaker")}n.breaks&&a.hasOwnProperty("eventBreaks")&&a.eventBreaks!==false&&a.eventBreaks.length>0&&V(a.eventBreaks)},jumpToTopic:function(a){a=H.topic[a];a!==undefined?C(a):h(j.no_timestamp,true)}};return d}(jQuery);