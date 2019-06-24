<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;


require __DIR__ . '/../../../vendor/autoload.php';

# Imports the Google Cloud client library
use Google\Cloud\Translate\TranslateClient;

# Imports the Google Cloud client library
use Google\Cloud\Speech\V1\SpeechClient;
use Google\Cloud\Speech\V1\RecognitionAudio;
use Google\Cloud\Speech\V1\RecognitionConfig;
use Google\Cloud\Speech\V1\RecognitionConfig\AudioEncoding;

use EasyWeChat\Factory;


class WechatController extends Controller
{  

    public function vi()
    {             
        \Log::debug('WechatController vi ..');

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

        $user = $app->oauth->user();
        \Log::debug($user);                 

        $app->server->push(function ($message) use ($app) {

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
                    # The text to translate
                    $text = $message['Content'];
                    break;
                case 'image':
                    break;
                case 'voice':
                    \Log::debug('收到语音消息');  
                    $text = $message['Recognition'];

                    \Log::debug($message);                    

                    // $date = date("Ymdhms");
                    // list($usec, $sec) = explode(" ", microtime());  
                    // $msec=round($usec*1000);  
                    // $millisecond = str_pad($msec,3,'0',STR_PAD_RIGHT);
                    // $timestring = $date.$millisecond;

                    // \Log::debug("audio".$timestring.".raw");
                    // \Log::debug($message['MediaId']);

                    // $stream = $app->media->get($message['MediaId']);

                    // # The audio file's encoding, sample rate and language
                    // $config = new RecognitionConfig([
                    //     'encoding' => AudioEncoding::AMR,
                    //     'sample_rate_hertz' => 8000,
                    //     'language_code' => 'en-US'
                    // ]);

                    // # Instantiates a client
                    // $client = new SpeechClient();

                    // # Detects speech in the audio file
                    // $response = $client->recognize($config, $stream);
                    // \Log::debug($response);

                    // # Print most likely transcription
                    // foreach ($response->getResults() as $result) {
                    //     $alternatives = $result->getAlternatives();
                    //     $mostLikely = $alternatives[0];
                    //     $transcript = $mostLikely->getTranscript();
                    //     // printf('Transcript: %s' . PHP_EOL, $transcript);

                    //     \Log::debug($transcript);  

                        
                    // }

                    // \Log::debug('保存语音消息');  

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

            \Log::debug($text);

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

                    $translation = $translate->translate($text, [
                        'target' => $target
                    ]);
                    \Log::debug($translation);

                    $result = '【'.$text.'】 所对应越南语的意思是：'.$translation['text'];
                    \Log::debug($result);
                }else if($detectResult['languageCode'] === 'vi'){
                     # The target language
                     $target = 'zh-CN';

                     $translation = $translate->translate($text, [
                         'target' => $target
                     ]);
                     \Log::debug($translation);                    
 
                     $result = '【'.$text.'】 所对应中文的意思是：'.$translation['text'];
                }
                
                //TODO 发送语音
                $audio = $app->media->uploadVoice('/home/forge/default/public/translate/output.mp3');
                \Log::debug($audio['media_id']);  

            }
            
            \Log::debug('test multi msg');
            $news1 = $result;
            
            return $news1;
        });

        $response = $app->server->serve();    
        // \Log::debug($response);

        return $response;
    }
}
