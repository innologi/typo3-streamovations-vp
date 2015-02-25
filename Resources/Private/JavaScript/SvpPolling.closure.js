/*
 http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
*/
var SvpPolling=function(g){function e(a){console.log("SVPP | "+a)}function h(a){g.get(i+(a===true?"&force=1":""),function(d){g.isEmptyObject(d)||SvpStarter.processMeetingdataChange(d);b=0},"json").fail(function(){b++;if(b>=k){e(b+" "+f.fail_limit,true);j.stop();b=0}})}var c=null,i=null,b=0,k=5,f={poll_start:"Polling started",poll_stop:"Polling stopped",fail_limit:"successive polling failures"},j={init:function(a,d,l){i=document.baseURI+"index.php?id="+d+"&eID=streamovations_vp_meetingdata&hash="+
a;if(c===null){e(f.poll_start,false);c=setInterval(function(){h(false)},l);h(true)}},stop:function(){if(c!==null){clearInterval(c);c=null;e(f.poll_stop,false)}}};return j}(jQuery);