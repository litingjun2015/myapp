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
            'aes_key' => 'JH9XRkdMzta8CgcAc8FuCqSqSNwiAbH3pKZROdRnbzq',
            'response_type' => 'array',

             /**
             * 日志配置
             *
             * level: 日志级别, 可选为：
             *         debug/info/notice/warning/error/critical/alert/emergency
             * path：日志文件位置(绝对路径!!!)，要求可写权限
             */
            'log' => [
                'default' => 'dev', // 默认使用的 channel，生产环境可以改为下面的 prod
                'channels' => [
                    // 测试环境
                    'dev' => [
                        'driver' => 'single',
                        'path' => '/tmp/easywechat.log',
                        'level' => 'debug',
                    ],
                    // 生产环境
                    'prod' => [
                        'driver' => 'daily',
                        'path' => '/tmp/easywechat.log',
                        'level' => 'info',
                    ],
                ],
            ],
        ];

        $app = Factory::officialAccount($config);

        \Log::debug('logging..');


        $app->server->push(function ($message) {

            //TODO
            putenv('GOOGLE_APPLICATION_CREDENTIALS='.resource_path().'/google.credentials.json');
        
            # Your Google Cloud Platform project ID
            $projectId = 'starlit-granite-20190622';

            # Instantiates a client
            $translate = new TranslateClient([
                'projectId' => $projectId
            ]);

            # The text to translate
            $text = $message['Content'];
            # The target language
            $target = 'vi';

            # Translates some text into Russian
            $translation = $translate->translate($text, [
                'target' => $target
            ]);

            $result = '【'.$message['Content'].'】 所对应越南语的意思是：'.$translation['text'];

            //TODO 发送语音
            
            return $result;
        });

        $response = $app->server->serve();    
        \Log::debug($response);

        return $response;
    }
}
