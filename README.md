# wsdebug
swoole 开发调试

![img](https://ws2.sinaimg.cn/large/006tNbRwly1fuswtvkc28j31kw0u2wql.jpg)

## 安装
```sh
composer require easyswoole/wsdebug
```

## 使用
EasySwoole Demo：
```php
// 在路由里注册个访问地址
// App/HttpController/Router.php
<?php
namespace App\HttpController;

use FastRoute\RouteCollector;
use EasySwoole\Core\Http\Request;
use EasySwoole\Core\Http\Response;
use EasySwoole\Core\Swoole\ServerManager;


class Router extends \EasySwoole\Core\Http\AbstractInterface\Router
{
	function register( RouteCollector $routeCollector )
	{
		$routeCollector->get( '/wsdebug', function( Request $request, Response $response ){
			$wsdebug = new \wsdebug\WsDebug();
			// 设置 swoole 服务
			$wsdebug->setServer( ServerManager::getInstance()->getServer() );
			// 设置输出html里的websocket服务地址
			$wsdebug->setHost( 'ws://127.0.0.1:9501' );
			// 输出调试工具的html
			$response->write( $wsdebug->getHtml() );
			$response->end();
		} );
	}
}
```
> 访问：http://127.0.0.1:9510/wsdebug 查看，具体端口号使用注册时的swoole端口号，这里只是演示

发送错误日志到界面：
```php
<?php
$wsdebug = new \wsdebug\WsDebug();
$wsdebug->send(['错误信息'=>'我是错误信息，为了测试输出到前台'],'error');

```
