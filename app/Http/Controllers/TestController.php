<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

require __DIR__ . '/../../../vendor/autoload.php';

# Imports the Google Cloud client library
use Google\Cloud\Translate\TranslateClient;

# Your Google Cloud Platform project ID


class TestController extends Controller
{
    public function index()
    {
        putenv('GOOGLE_APPLICATION_CREDENTIALS='.resource_path().'/google.credentials.json');
        
        $projectId = 'starlit-granite-20190622';

        # Instantiates a client
        $translate = new TranslateClient([
            'projectId' => $projectId
        ]);

        # The text to translate
        $text = 'Hello, world!';
        # The target language
        $target = 'cn';

        # Translates some text into Russian
        $translation = $translate->translate($text, [
            'target' => $target
        ]);

        echo 'Text: ' . $text . '
        Translation: ' . $translation['text'];

        


        return  $translation['text'];
    }
}
