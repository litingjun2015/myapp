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
use Google\Cloud\Speech\V1\RecognitionConfig\AudioEncoding;
use Google\Cloud\TextToSpeech\V1\AudioConfig;
use Google\Cloud\TextToSpeech\V1\AudioEncoding as TextToSpeechAudioEncoding;
use Google\Cloud\TextToSpeech\V1\SsmlVoiceGender;
use Google\Cloud\TextToSpeech\V1\SynthesisInput;
use Google\Cloud\TextToSpeech\V1\TextToSpeechClient;
use Google\Cloud\TextToSpeech\V1\VoiceSelectionParams;

class Index
{
    public function index()
    {
        echo $this->translate('你好');
        // $content = file_get_contents('./voice.amr');
        // echo $this->speech2Text($content);
        // echo $this->text2Speech('Hello');
    }

    /**
     * 文本消息回调
     */
    private function callbackText($message, $app) 
    {
        $content = '翻译.';
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
     * 文本翻译 translate
     */
    private function translate($text, $target = 'en') 
    {
        // 设置 Google 授权文件环境变量
        $this->setEnvGoogleCredentials();

        # Your Google Cloud Platform project ID
        $projectId = 'starlit-granite-20190622';

        # Instantiates a client
        $translate = new TranslateClient([
            'projectId' => $projectId
        ]);

        # Translates some text into target language
        $translation = $translate->translate($text, [
            'target' => $target
        ]);

        return $translation['text'];
    }

    /**
     * 语音转文本
     */
    private function speech2Text($content, $language = 'zh-CN', $encoding = AudioEncoding::AMR, $sampleRate = 8000) 
    {
         // 设置 Google 授权文件环境变量
         $this->setEnvGoogleCredentials();

        # set string as audio content
        $audio = (new RecognitionAudio())
        ->setContent($content);
    
        # The audio file's encoding, sample rate and language
        $config = new RecognitionConfig([
            'encoding' => $encoding,
            'sample_rate_hertz' => $sampleRate,
            'language_code' => $language
        ]);
        
        # Instantiates a client
        $client = new SpeechClient();
        
        # Detects speech in the audio file
        $response = $client->recognize($config, $audio);
        
        # Print most likely transcription
        foreach ($response->getResults() as $result) {
            $alternatives = $result->getAlternatives();
            $mostLikely = $alternatives[0];
            $transcript = $mostLikely->getTranscript();
        }
        
        $client->close();

        return $transcript;
    }

    private function text2Speech($text, $language = 'en-US', $path = './upload/voice') 
    {
         // 设置 Google 授权文件环境变量
         $this->setEnvGoogleCredentials();
        
        // instantiates a client
        $client = new TextToSpeechClient();

        // sets text to be synthesised
        $synthesisInputText = (new SynthesisInput())
            ->setText($text);

        // build the voice request, select the language code ("en-US") and the ssml
        // voice gender
        $voice = (new VoiceSelectionParams())
            ->setLanguageCode($language)
            ->setSsmlGender(SsmlVoiceGender::FEMALE);

        // Effects profile
        $effectsProfileId = "telephony-class-application";

        // select the type of audio file you want returned
        $audioConfig = (new AudioConfig())
            ->setAudioEncoding(TextToSpeechAudioEncoding::MP3)
            ->setEffectsProfileId(array($effectsProfileId));

        // perform text-to-speech request on the text input with selected voice
        // parameters and audio file type
        $response = $client->synthesizeSpeech($synthesisInputText, $voice, $audioConfig);
        $audioContent = $response->getAudioContent();

        // the response's audioContent is binary
        $file = $path . '/' . date('Ymdhms') . '.mp3';
        file_put_contents($file, $audioContent);

        return $file;
    }
}
