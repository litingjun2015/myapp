<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;


require __DIR__ . '/../../../vendor/autoload.php';

# Imports the Google Cloud client library
use Google\Cloud\Translate\TranslateClient;


use EasyWeChat\Factory;


class WechatController extends Controller
{  

    public function vi()
    {                
        $config = [
            // 客户
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

            switch ($message['MsgType']) {
                case 'event':
                    break;
                case 'text':
                    \Log::debug('收到文字消息');
                    break;
                case 'image':
                    break;
                case 'voice':
                    \Log::debug('收到语音消息');  

                    $date = date("Ymdhms");
                    list($usec, $sec) = explode(" ", microtime());  
                    $msec=round($usec*1000);  
                    $millisecond = str_pad($msec,3,'0',STR_PAD_RIGHT);
                    $timestring = $date.$millisecond;

                    \Log::debug("audio".$timestring.".raw");
                    \Log::debug($message['MediaId']);

                    $stream = $app->media->get($message['MediaId']);

                    if ($stream instanceof \EasyWeChat\Kernel\Http\StreamResponse) {
                        // // 以内容 md5 为文件名存到本地
                        // $stream->save('保存目录');

                        // 自定义文件名，不需要带后缀
                        $stream->saveAs('/tmp/', "audio".$timestring.".raw");
                    }

                    break;
                case 'video':                
                    break;
                case 'location':
                    break;
                case 'link':
                    break;
                // ... 其它消息
                default:
                    break;
            }


        


            # The text to translate
            $text = $message['Content'];

            if($text === '汇率') {
                $result = sprintf("%-48s%-40s%-66s%-46s%-40s%-66s%-46s%-40s", 
                "1美元=6.8698人民币", "1人民币=0.1456美元", "", "1越南盾=0.00030人民币", "1人民币=3364.11越南盾", "","1美元=23137.75越南盾", "1越南盾=0.000043美元" );
            } else {

                # 语言检测
                $detectResult = $translate->detectLanguage($text);
                \Log::debug("Language code: $detectResult[languageCode]\n");
                \Log::debug("Confidence: $detectResult[confidence]\n");

                if($detectResult['languageCode'] === 'zh-CN'){
                    # The target language
                    $target = 'vi';

                    # Translates some text into Russian
                    $translation = $translate->translate($text, [
                        'target' => $target
                    ]);
                    \Log::debug($translation);

                    $result = '【'.$message['Content'].'】 所对应越南语的意思是：'.$translation['text'];
                }else if($detectResult['languageCode'] === 'vi'){
                     # The target language
                     $target = 'zh-CN';

                     # Translates some text into Russian
                     $translation = $translate->translate($text, [
                         'target' => $target
                     ]);
                     \Log::debug($translation);                    
 
                     $result = '【'.$message['Content'].'】 所对应中文的意思是：'.$translation['text'];
                }
                

            }
            
            //TODO 发送语音
            
            return $result;
        });

        $response = $app->server->serve();    
        \Log::debug($response);

        return $response;
    }
}
