<?php
namespace app\index\controller;

use EasyWeChat\Kernel\Messages\Text;
use EasyWeChat\Kernel\Messages\Image;
use EasyWeChat\Kernel\Messages\Video;
use EasyWeChat\Kernel\Messages\Voice;
use EasyWeChat\Kernel\Messages\News;
use EasyWeChat\Kernel\Messages\NewsItem;
use EasyWeChat\Kernel\Messages\Article;
use EasyWeChat\Kernel\Messages\Media;

# Imports the Google Cloud client library
use Google\Cloud\Translate\TranslateClient;
use Google\Cloud\Speech\V1\SpeechClient;
use Google\Cloud\Speech\V1\RecognitionAudio;
use Google\Cloud\Speech\V1\RecognitionConfig;
use Google\Cloud\TextToSpeech\V1\AudioConfig;
use Google\Cloud\TextToSpeech\V1\SsmlVoiceGender;
use Google\Cloud\TextToSpeech\V1\SynthesisInput;
use Google\Cloud\TextToSpeech\V1\TextToSpeechClient;
use Google\Cloud\TextToSpeech\V1\VoiceSelectionParams;

define('AUDIO_ENCODING_AMR', \Google\Cloud\Speech\V1\RecognitionConfig\AudioEncoding::AMR);
define('AUDIO_ENCODING_MP3', \Google\Cloud\TextToSpeech\V1\AudioEncoding::MP3);

class Index
{
    public function index()
    {
        echo $this->translate('你几岁了?');
        $content = file_get_contents('./voice.amr');
        echo $this->speech2Text($content);
        echo $this->text2Speech('how old are you?');
    }

    /**
     * 文本消息回调
     */
    private function callbackText($message, $app) 
    {
        $content = '翻译';
        // // 翻译成默认语言
        // $content = $this->translate($message['Content']);

        // // 文本转为语音, 并保存在默认目录
        // $this->text2Speech($content);

        // 创建文本消息
        $text = new Text($content);

        return $text;
    } 

    /**
     * 图片消息回调
     */
    private function callbackImage($message, $app) 
    {
        // 创建图片消息
        $image = new Image($message['MediaId']);

        return $image;
    }

    /**
     * 
     */
    private function callbackVoice($message, $app)
    {
        // 创建文本消息
        $text = new Text('对不起, 请再说一遍.');

        // 获取用户发送语音文件, 并保存到本地
        $stream = $app->media->get($message['MediaId']);
        if ($stream instanceof \EasyWeChat\Kernel\Http\StreamResponse) {
            // 获取用户语音内容, 并转文本
            $content = $stream->getBody()->getContents();
            $content = $this->speech2Text($content);

            // 修改文本消息内容
            $text->content = $content;
        }

        return $text;
    }

    /**
     * 设置 Google 授权文件环境变量
     */
    private function setEnvGoogleCredentials() 
    {
        putenv('GOOGLE_APPLICATION_CREDENTIALS=./google.credentials.json');
    }

    /**
     * 文本翻译
     */
    private function translate($text, $target = 'en') {
        // 设置 Google 授权文件环境变量
        $this->setEnvGoogleCredentials();

        # Google Cloud Platform 项目编号
        $projectId = 'starlit-granite-20190622';

        # 初始化客户客实例
        $translate = new TranslateClient([
            'projectId' => $projectId
        ]);

        # 将文本内容翻译为目标语语言
        $translation = $translate->translate($text, [
            'target' => $target
        ]);

        return $translation['text'];
    }

    /**
     * 语音转文本
     */
    private function speech2Text($content, $language = 'zh-CN', $encoding = AUDIO_ENCODING_AMR, $sampleRate = 8000) {
         // 设置 Google 授权文件环境变量
         $this->setEnvGoogleCredentials();

        # 设置语音内容
        $audio = (new RecognitionAudio())
        ->setContent($content);
    
        # 设置语音属性: 编码、波特率、语言
        $config = new RecognitionConfig([
            'encoding' => $encoding,
            'sample_rate_hertz' => $sampleRate,
            'language_code' => $language
        ]);
        
        # 初始化客户端实例
        $client = new SpeechClient();
        
        # 检测语音, 获取返回结果
        $response = $client->recognize($config, $audio);
        
        # 转换为文本
        foreach ($response->getResults() as $result) {
            $alternatives = $result->getAlternatives();
            $mostLikely = $alternatives[0];
            $transcript = $mostLikely->getTranscript();
        }
        // 关闭实例对象
        $client->close();

        return $transcript;
    }

    private function text2Speech($text, $language = 'en-US', $path = './upload/voice') {
         // 设置 Google 授权文件环境变量
         $this->setEnvGoogleCredentials();
        
        // 初始化客户端实例
        $client = new TextToSpeechClient();

        // 设置文本内容
        $synthesisInputText = (new SynthesisInput())
            ->setText($text);

        // 创建语音请求, 选择语言, 语音性别
        $voice = (new VoiceSelectionParams())
            ->setLanguageCode($language)
            ->setSsmlGender(SsmlVoiceGender::FEMALE);

        // 效果模式
        $effectsProfileId = "telephony-class-application";

        // 设置返回文件类型
        $audioConfig = (new AudioConfig())
            ->setAudioEncoding(AUDIO_ENCODING_MP3)
            ->setEffectsProfileId(array($effectsProfileId));

        // 请求语音内容
        $response = $client->synthesizeSpeech($synthesisInputText, $voice, $audioConfig);
        $audioContent = $response->getAudioContent();

        // 将语音内容保存为文件
        $file = $path . '/' . date('Ymdhms') . '.mp3';
        file_put_contents($file, $audioContent);

        return $file;
    }
}
