<?php
/**
 * Created by PhpStorm.
 * User: Jaeger <JaegerCode@gmail.com>
 * Date: 2017/9/30
 * Use PhantomJS to crawl Javascript dynamically rendered pages.
 */

namespace QL\Ext;


use JonnyW\PhantomJs\Http\RequestInterface;
use QL\Contracts\PluginContract;
use QL\QueryList;
use JonnyW\PhantomJs\Client;
use Closure;

class PhantomJs implements PluginContract
{
    protected static $browser = null;

    public static function install(QueryList $queryList, ...$opt)
    {
        // PhantomJS bin path
        $phantomJsBin = $opt[0];
        $name = $opt[1] ?? 'browser';
        $queryList->bind($name,function ($request,$commandOpt = []) use($phantomJsBin){
            return PhantomJs::render($this,$phantomJsBin,$request,$commandOpt);
        });
        
    }

    public static function render(QueryList $queryList,$phantomJsBin,$url,$commandOpt = [])
    {
        $client = self::getBrowser($phantomJsBin,$commandOpt);
        $request = $client->getMessageFactory()->createRequest();
        if($url instanceof Closure){
            $request = $url($request);
        }else{
            $request->setMethod('GET');
            $request->setUrl($url);
        }
        $response = $client->getMessageFactory()->createResponse();
        $client->send($request, $response);
        $html = '<html>'.$response->getContent().'</html>';
        $queryList->setHtml($html);
        return $queryList;
    }

    protected static function getBrowser($phantomJsBin,$commandOpt)
    {
        if(self::$browser == null){
            self::$browser = Client::getInstance();
            self::$browser->getEngine()->setPath($phantomJsBin);
            self::$browser->getEngine()->addOption('--load-images=false');
            self::$browser->getEngine()->addOption('--ignore-ssl-errors=true');
        }
        foreach ($commandOpt as $k => $v) {
            $str = sprintf('%s=%s',$k,$v);
            print_r($str);
            self::$browser->getEngine()->addOption($str);
        }
        return self::$browser;
    }

}