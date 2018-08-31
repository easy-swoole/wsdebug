<?php
/**
 *
 * User: hanwenbo
 * Date: 2018/8/31
 * Time: 下午2:26
 *
 */
namespace wsdebug;

class WsDebug
{
	/**
	 * @var \swoole_websocket_server
	 */
	private $server;
	/**
	 * @var string
	 */
	private $host;

	/**
	 * @param \swoole_websocket_server $server
	 */
	public function setServer( \swoole_websocket_server $server ) : void
	{
		$this->server = $server;
	}

	/**
	 * @param $host
	 */
	public function setHost( $host ) : void
	{
		$this->host = $host;
	}

	/**
	 * @param mixed  $message
	 * @param string $type 用于前端标记
	 * @return bool
	 */
	public function send( $message, string $type = 'info' )
	{
		$server = $this->server;
		if( !empty( $server->connections ) ){
			foreach( $server->connections as $fd ){
				$info = $server->connection_info( $fd );
				if( isset( $info['websocket_status'] ) && $info['websocket_status'] === 3 ){
					$message      = $this->object2array( $message );
					$json_message = json_encode( [
						'type'    => $type,
						'time'    => date( "Y-m-d H:i:s" ),
						'content' => $message,
					]);
					$server->push( $fd, $json_message );
				}
			}
			return true;
		} else{
			return false;
		}
	}

	private function object2array( $object )
	{
		if( is_object( $object ) ){
			$object = (array)$object;
		}
		if( is_array( $object ) ){
			foreach( $object as $key => $value ){
				$object[$key] = $this->object2array( $value );
			}
		}
		return $object;
	}


	/**
	 * Convert encoding.
	 *
	 * @param array  $array
	 * @param string $to_encoding
	 * @param string $from_encoding
	 *
	 * @return array
	 */
	private function encoding( $array, $to_encoding = 'UTF-8', $from_encoding = 'GBK' ) : array
	{
		$encoded = [];
		foreach( $array as $key => $value ){
			if( is_array( $value ) ){
				$encoded[$key] = $this->encoding( $value, $to_encoding, $from_encoding );
			} elseif( is_bool( $value ) ){
				$encoded[$key] = $value;
			} elseif( is_string( $value ) && mb_detect_encoding( $value, 'UTF-8', true ) ){
				$encoded[$key] = $value;
			} else{
				$encoded[$key] = mb_convert_encoding( $value, $to_encoding, $from_encoding );
			}
		}
		return $encoded;
	}

	/**
	 * @return string
	 */
	public function getHtml() : string
	{
		$host = $this->host;
		$html
		      = <<<EOT
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Nothing</title>
</head>
<body>
<script type="text/javascript" src="https://cdn.bootcss.com/jquery/3.2.1/jquery.min.js"></script>
<div id="msg"></div>
<a href="javascript:void(0)" onclick="clearLog()" id="clear_action">清空</a>
</body>
<style>
    #clear_action{
        position: fixed;
        bottom: 20px;
        right: 20px;
        color: #fff;
        text-decoration: none;
        background-color: #131312;
        width: 60px;
        height: 60px;
        line-height: 60px;
        text-align: center;
        border-radius: 10em;
        border: 1px solid #0d0d0d;
    }
    body{font-size:12px;background-color:#272822;color:#fff;padding:20px 20px;}
    #log-container{word-wrap: break-word;}
    .hide{display:none}
    .row{margin:5px 0}
    .row .font{display:inline}
    .row textarea{width:100%;}
    .row em{font-style:normal;color:#8F908A;margin-right:20px;}
    .row_extend{display: inline-block;}
    .btn{    color: #fff;
        background-color: #521818;
        border: 1px solid #000;}
    .SESSION{color:#00ffd0}
    .LANG,.CACHE,.RUN{}
    .EMERG{ color:red;}
    .error{ color:#b99090;}
    .sql{color:#438646}
    .ALERT{color:red;}
    .CRIT{color:red}
    .ERR{color:red}
    .WARN{color:yellow}
    .NOTIC{color:#E6DB74}
    .INFO{color:#5a5a5a}
    .DEBUG{color:#3f0;}
    .SQL,.DB{color:#878a00}
    .TIME{color:#329fff;padding-top:10px;border-top:7px solid #4B922E}
    .copyright{color: #52D9B1;text-align: center;display: block;text-decoration: none;margin:50px 0}
    .intro p{ color:#8F908A;}
    .intro a{color: #52D9B1;text-decoration: none;}
    .CLIENT_TRACE{}
    .HEADER{}
    .GET{}
    .POST{}
    .jsonview{font-family:monospace;font-size:1em;white-space:pre-wrap}.jsonview .prop{font-weight:400;text-decoration:none;color:#379325}.jsonview .null,.jsonview .undefined{color:red}.jsonview .bool,.jsonview .num{color:#33ff00}.jsonview .string{color:#fff;white-space:pre-wrap}.jsonview .string.multiline{display:inline-block;vertical-align:text-top}.jsonview .collapser{position:absolute;left:-1em;cursor:pointer}.jsonview .collapsible{transition:height 1.2s;transition:width 1.2s}.jsonview .collapsible.collapsed{height:.8em;width:1em;display:inline-block;overflow:hidden;margin:0}.jsonview .collapsible.collapsed:before{content:"…";width:1em;margin-left:.2em}.jsonview .collapser.collapsed{transform:rotate(0)}.jsonview .q{display:inline-block;width:0;color:transparent}.jsonview li{position:relative}.jsonview li a{color:#33ff00}.jsonview ul{list-style:none;margin:0 0 0 2em;padding:0}.jsonview h1{font-size:1.2em}</style>
<script>!function(e){var t,n,r,l,o;return o=["object","array","number","string","boolean","null"],r=function(){function t(e){null==e&&(e={}),this.options=e}return t.prototype.htmlEncode=function(e){return null!==e?e.toString().replace(/&/g,"&amp;").replace(/"/g,"&quot;").replace(/</g,"&lt;").replace(/>/g,"&gt;"):""},t.prototype.jsString=function(e){return e=JSON.stringify(e).slice(1,-1),this.htmlEncode(e)},t.prototype.decorateWithSpan=function(e,t){return'<span class="'+t+'">'+this.htmlEncode(e)+"</span>"},t.prototype.valueToHTML=function(t,n){var r;if(null==n&&(n=0),r=Object.prototype.toString.call(t).match(/\s(.+)]/)[1].toLowerCase(),this.options.strict&&!e.inArray(r,o))throw new Error(""+r+" is not a valid JSON value type");return this[""+r+"ToHTML"].call(this,t,n)},t.prototype.nullToHTML=function(e){return this.decorateWithSpan("null","null")},t.prototype.undefinedToHTML=function(){return this.decorateWithSpan("undefined","undefined")},t.prototype.numberToHTML=function(e){return this.decorateWithSpan(e,"num")},t.prototype.stringToHTML=function(e){var t,n;return/^(http|https|file):\/\/[^\s]+$/i.test(e)?'<a href="'+this.htmlEncode(e)+'"><span class="q">"</span>'+this.jsString(e)+'<span class="q">"</span></a>':(t="",e=this.jsString(e),this.options.nl2br&&(n=/([^>\\r\\n]?)(\\r\\n|\\n\\r|\\r|\\n)/g,n.test(e)&&(t=" multiline",e=(e+"").replace(n,"$1<br />"))),'<span class="string'+t+'">"'+e+'"</span>')},t.prototype.booleanToHTML=function(e){return this.decorateWithSpan(e,"bool")},t.prototype.arrayToHTML=function(e,t){var n,r,l,o,i,s,a,p;for(null==t&&(t=0),r=!1,i="",o=e.length,l=a=0,p=e.length;p>a;l=++a)s=e[l],r=!0,i+="<li>"+this.valueToHTML(s,t+1),o>1&&(i+=","),i+="</li>",o--;return r?(n=0===t?"":" collapsible",'[<ul class="array level'+t+n+'">'+i+"</ul>]"):"[ ]"},t.prototype.objectToHTML=function(e,t){var n,r,l,o,i,s,a;null==t&&(t=0),r=!1,i="",o=0;for(s in e)o++;for(s in e)a=e[s],r=!0,l=this.options.escape?this.jsString(s):s,i+='<li><a class="prop" href="javascript:;"><span class="q">"</span>'+l+'<span class="q">"</span></a>: '+this.valueToHTML(a,t+1),o>1&&(i+=","),i+="</li>",o--;return r?(n=0===t?"":" collapsible",'{<ul class="obj level'+t+n+'">'+i+"</ul>}"):"{ }"},t.prototype.jsonToHTML=function(e){return'<div class="jsonview">'+this.valueToHTML(e)+"</div>"},t}(),"undefined"!=typeof module&&null!==module&&(module.exports=r),n=function(){function e(){}return e.bindEvent=function(e,t){var n;return e.firstChild.addEventListener("click",function(e){return function(n){return e.toggle(n.target.parentNode.firstChild,t)}}(this)),n=document.createElement("div"),n.className="collapser",n.innerHTML=t.collapsed?"+":"-",n.addEventListener("click",function(e){return function(n){return e.toggle(n.target,t)}}(this)),e.insertBefore(n,e.firstChild),t.collapsed?this.collapse(n):void 0},e.expand=function(e){var t,n;return n=this.collapseTarget(e),""!==n.style.display?(t=n.parentNode.getElementsByClassName("ellipsis")[0],n.parentNode.removeChild(t),n.style.display="",e.innerHTML="-"):void 0},e.collapse=function(e){var t,n;return n=this.collapseTarget(e),"none"!==n.style.display?(n.style.display="none",t=document.createElement("span"),t.className="ellipsis",t.innerHTML=" &hellip; ",n.parentNode.insertBefore(t,n),e.innerHTML="+"):void 0},e.toggle=function(e,t){var n,r,l,o,i,s;if(null==t&&(t={}),l=this.collapseTarget(e),n="none"===l.style.display?"expand":"collapse",t.recursive_collapser){for(r=e.parentNode.getElementsByClassName("collapser"),s=[],o=0,i=r.length;i>o;o++)e=r[o],s.push(this[n](e));return s}return this[n](e)},e.collapseTarget=function(e){var t,n;return n=e.parentNode.getElementsByClassName("collapsible"),n.length?t=n[0]:void 0},e}(),t=e,l={collapse:function(e){return"-"===e.innerHTML?n.collapse(e):void 0},expand:function(e){return"+"===e.innerHTML?n.expand(e):void 0},toggle:function(e){return n.toggle(e)}},t.fn.JSONView=function(){var e,o,i,s,a,p,c;return e=arguments,null!=l[e[0]]?(a=e[0],this.each(function(){var n,r;return n=t(this),null!=e[1]?(r=e[1],n.find(".jsonview .collapsible.level"+r).siblings(".collapser").each(function(){return l[a](this)})):n.find(".jsonview > ul > li .collapsible").siblings(".collapser").each(function(){return l[a](this)})})):(s=e[0],p=e[1]||{},o={collapsed:!1,nl2br:!1,recursive_collapser:!1,escape:!0,strict:!1},p=t.extend(o,p),i=new r(p),"[object String]"===Object.prototype.toString.call(s)&&(s=JSON.parse(s)),c=i.jsonToHTML(s),this.each(function(){var e,r,l,o,i,s;for(e=t(this),e.html(c),l=e[0].getElementsByClassName("collapsible"),s=[],o=0,i=l.length;i>o;o++)r=l[o],"LI"===r.parentNode.nodeName?s.push(n.bindEvent(r.parentNode,p)):s.push(void 0);return s}))}}(jQuery);</script>
<script>
    function clearLog(){
        $("#msg").html('');
    }
    var line_num = 0;
    var wsServer = "{$host}";
    var socket;
    var socketState = {
        isConnection : false
    };
    var initWebSocket = function() {
        if (window.WebSocket) {
            socket = new WebSocket(wsServer);
            socket.onopen = function(event) {
                socketState.isConnection = true;
            };
            socket.onmessage = function(event) {
                var style =  '';
                var data = JSON.parse(event.data);
                if(data.type !== 'pong' ){
                    if(typeof(data.type) === "undefined" || data.type === "error"){
                        style = 'color:red';
                    }
                    log($("<div class='log' style='"+style+"'></div>").JSONView(event.data,{ collapsed: false }));
                    log("<div class='line_num'>第 "+line_num+" 行</div>");
                    line_num ++ ;
                }
                if(data.type === 'pong' ){
                    console.log("保持心跳");
                }
            };
            socket.onclose = function(event) {
                socketState.isConnection = false;
                log("<p>已断开</p>");
            };
            socket.onerror = function(event) {
                log($("<div class='log'>" + evt.data + "</div>"));
            };
        } else {
            log("你的浏览器不支持WebSocket.");
        }
    }
    function log(msg){
        $('#msg').prepend(msg);
    }
    initWebSocket();
    setInterval(function(){
        if(socketState.isConnection !== true){
            initWebSocket();
            log("<p>正在尝试重链...</p>");
        }
        socket.send("pong");
    },3000);
</script>
</html>
EOT;
		return $html;
	}
}