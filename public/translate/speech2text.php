<?php
# Includes the autoloader for libraries installed with composer
require __DIR__ . '/vendor/autoload.php';

# Imports the Google Cloud client library
use Google\Cloud\Speech\V1\SpeechClient;
use Google\Cloud\Speech\V1\RecognitionAudio;
use Google\Cloud\Speech\V1\RecognitionConfig;
use Google\Cloud\Speech\V1\RecognitionConfig\AudioEncoding;

putenv('GOOGLE_APPLICATION_CREDENTIALS=' . __DIR__ . '/google.credentials.json');

# The name of the audio file to transcribe
$audioFile = __DIR__ . '/audio32KHz.amr';


# get contents of a file into a string
$content = file_get_contents($audioFile);

# set string as audio content
$audio = (new RecognitionAudio())
    ->setContent($content);

# The audio file's encoding, sample rate and language
$config = new RecognitionConfig([
    'encoding' => AudioEncoding::AMR,
    'sample_rate_hertz' => 8000,
    'language_code' => 'en-US'
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
    printf('Transcript: %s' . PHP_EOL, $transcript);
}

$client->close();