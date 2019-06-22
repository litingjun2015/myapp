<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

require __DIR__ . '/../../../vendor/autoload.php';

# Imports the Google Cloud client library
use Google\Cloud\Translate\TranslateClient;


use EasyWeChat\Factory;


class TestController extends Controller
{
    public function index()
    {
        putenv('GOOGLE_APPLICATION_CREDENTIALS='.resource_path().'/google.credentials.json');
        
        # Your Google Cloud Platform project ID
        $projectId = 'starlit-granite-20190622';

        # Instantiates a client
        $translate = new TranslateClient([
            'projectId' => $projectId
        ]);

        # The text to translate
        $text = 'Hello, world!';
        # The target language
        $target = 'vi';

        # Translates some text into Russian
        $translation = $translate->translate($text, [
            'target' => $target
        ]);

        echo 'Text: ' . $text . '
        Translation: ' . $translation['text'];

        


        return  $translation['text'];
    }


    public function wechat()
    {        
        $config = [
            'app_id' => 'wx4fcd7ab419b697c2',
            'secret' => '313ef808ffed2c0dc14dc7807f81a165',
            'token' => 'TestToken',
            'response_type' => 'array',
        ];

        $app = Factory::officialAccount($config);


        $app->server->push(function ($message) {
            return "您好！欢迎使用 EasyWeChat!";
        });

        $response = $app->server->serve();    

        return $response;
    }
}
