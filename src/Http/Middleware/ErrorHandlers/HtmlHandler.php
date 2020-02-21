<?php

declare(strict_types=1);

namespace Frostal\Http\Middleware\ErrorHandlers;

use Frostal\Http\HttpException;
use Whoops\Handler\Handler;

class HtmlHandler extends Handler
{
    public function handle()
    {
        $exception = $this->getException();
        if (!($exception instanceof HttpException)) {
            $exception = new HttpException(500);
        }

        echo $this->renderHtmlBody($exception->getCode(), $exception->getMessage());
        return Handler::QUIT;
    }

    /**
     * @return string
     */
    public function contentType()
    {
        return 'text/html';
    }

    /**
     * @param string $title
     * @param string $html
     * @return string
     */
    private function renderHtmlBody(int $code, string $message): string
    {
        return
            // @codingStandardsIgnoreStart
            "<!DOCTYPE html>\n" .
            "<html>\n" .
            "   <head>\n" .
            "       <meta http-equiv='Content-Type' content='text/html; charset=utf-8'>\n" .
            "       <title>$code - $message</title>\n" .
            "       <link href='https://fonts.googleapis.com/css?family=Encode+Sans+Semi+Condensed:100,200,300,400' rel='stylesheet'>" .
            "       <style>\n" .
            "           @-webkit-keyframes clockwiseError{0%{-webkit-transform:rotate(0)}20%{-webkit-transform:rotate(30deg)}40%{-webkit-transform:rotate(25deg)}60%{-webkit-transform:rotate(30deg)}100%{-webkit-transform:rotate(0)}}\n" .
            "           @-webkit-keyframes anticlockwiseErrorStop{0%{-webkit-transform:rotate(0)}20%{-webkit-transform:rotate(-30deg)}60%{-webkit-transform:rotate(-30deg)}100%{-webkit-transform:rotate(0)}}\n" .
            "           @-webkit-keyframes anticlockwiseError{0%{-webkit-transform:rotate(0)}20%{-webkit-transform:rotate(-30deg)}40%{-webkit-transform:rotate(-25deg)}60%{-webkit-transform:rotate(-30deg)}100%{-webkit-transform:rotate(0)}}\n" .
            "           @keyframes clockwiseError{0%{transform:rotate(0)}20%{transform:rotate(30deg)}40%{transform:rotate(25deg)}60%{transform:rotate(30deg)}100%{transform:rotate(0)}}\n" .
            "           @keyframes anticlockwiseErrorStop{0%{transform:rotate(0)}20%{transform:rotate(-30deg)}60%{transform:rotate(-30deg)}100%{transform:rotate(0)}}\n" .
            "           @keyframes anticlockwiseError{0%{transform:rotate(0)}20%{transform:rotate(-30deg)}40%{transform:rotate(-25deg)}60%{transform:rotate(-30deg)}100%{transform:rotate(0)}}\n" .
            "           body{background:#eaeaea}\n" .
            "           h1{margin:100px auto 0 auto;color:#000;font-family:'Encode Sans Semi Condensed', Verdana, sans-serif;font-size:10rem;line-height:10rem;font-weight:200;text-align:center}\n" .
            "           h2{margin:20px auto 30px auto;font-family:'Encode Sans Semi Condensed', Verdana, sans-serif;font-size:1.5rem;font-weight:200;text-align:center}\n" .
            "           h1,h2{-webkit-transition:opacity .5s linear, margin-top .5s linear;-o-transition:opacity .5s linear, margin-top .5s linear;transition:opacity .5s linear, margin-top .5s linear;transition:opacity .5s linear, margin-top .5s linear}\n" .
            "           .gears{position:relative;margin:0 auto;width:auto;height:0}\n" .
            "           .gear{position:relative;z-index:0;width:120px;height:120px;margin:0 auto;border-radius:50%;background:#000}\n" .
            "           .gear:before{position:absolute;left:5px;top:5px;right:5px;bottom:5px;z-index:2;content:'';border-radius:50%;background:#eaeaea}\n" .
            "           .gear:after{position:absolute;left:25px;top:25px;z-index:3;content:'';width:70px;height:70px;border-radius:50%;border:5px solid #000;-webkit-box-sizing:border-box;box-sizing:border-box;background:#eaeaea}\n" .
            "           .gear.one{left:-130px}\n" .
            "           .gear.two{top:-75px}\n" .
            "           .gear.three{top:-235px;left:130px}\n" .
            "           .gear .bar{position:absolute;left:-15px;top:50%;z-index:0;width:150px;height:30px;margin-top:-15px;border-radius:5px;background:#000}\n" .
            "           .gear .bar:before{position:absolute;left:5px;top:5px;right:5px;bottom:5px;z-index:1;content:'';border-radius:2px;background:#eaeaea}\n" .
            "           .gear .bar:nth-child(2){-webkit-transform:rotate(60deg);-ms-transform:rotate(60deg);transform:rotate(60deg);transform:rotate(60deg)}\n" .
            "           .gear .bar:nth-child(3){-webkit-transform:rotate(120deg);-ms-transform:rotate(120deg);transform:rotate(120deg);transform:rotate(120deg)}\n" .
            "           .gear.one{-webkit-animation:anticlockwiseErrorStop 2s linear infinite;animation:anticlockwiseErrorStop 2s linear infinite}\n" .
            "           .gear.two{-webkit-animation:anticlockwiseError 2s linear infinite;animation:anticlockwiseError 2s linear infinite}\n" .
            "           .gear.three{-webkit-animation:clockwiseError 2s linear infinite;animation:clockwiseError 2s linear infinite}\n" .
            "       </style>\n" .
            "   </head>\n" .
            "   <body>\n" .
            "       <h1>$code</h1>\n" .
            "       <h2>$message</h2>\n" .
            "       <div class='gears'>\n" .
            "           <div class='gear one'>\n" .
            "               <div class='bar'></div>\n" .
            "               <div class='bar'></div>\n" .
            "               <div class='bar'></div>\n" .
            "           </div>\n" .
            "           <div class='gear two'>\n" .
            "               <div class='bar'></div>\n" .
            "               <div class='bar'></div>\n" .
            "               <div class='bar'></div>\n" .
            "           </div>\n" .
            "           <div class='gear three'>\n" .
            "               <div class='bar'></div>\n" .
            "               <div class='bar'></div>\n" .
            "               <div class='bar'></div>\n" .
            "           </div>\n" .
            "       </div>\n" .
            "   </body>\n" .
            "</html>";
            // @codingStandardsIgnoreEnd
    }
}
