<?php

use SergiX44\Gradio\Client;

it('can test', function () {
    $client = new Client('https://multimodalart-stable-cascade.hf.space');

    $response = $client->predict([
        'house', // string  in 'Prompt' Textbox component
        '!', // string  in 'Negative prompt' Textbox component
        0, // number (numeric value between 0 and 2147483647) in 'Seed' Slider component
        1024, // number (numeric value between 1024 and 1536) in 'Width' Slider component
        1024, // number (numeric value between 1024 and 1536) in 'Height' Slider component
        10, // number (numeric value between 10 and 30) in 'Prior Inference Steps' Slider component
        0, // number (numeric value between 0 and 20) in 'Prior Guidance Scale' Slider component
        4, // number (numeric value between 4 and 12) in 'Decoder Inference Steps' Slider component
        0, // number (numeric value between 0 and 0) in 'Decoder Guidance Scale' Slider component
        1, // number (numeric value between 1 and 2) in 'Number of Images' Slider component
    ], '/run');

    $outputs = $response->getOutputs();

    expect($client)->toBeInstanceOf(Client::class);
});

it('can test another model', function () {
    $client = new Client('https://ehristoforu-mixtral-46-7b-chat.hf.space');

    $client->predict([], fnIndex: 5, raw: true);
    $client->predict(['hi'], fnIndex: 1, raw: true);
    $client->predict([null, []], fnIndex: 2, raw: true);
    $response = $client->predict([null, null, '', 0.9, 256, 0.9, 1.2], fnIndex: 3);
    $client->predict([], fnIndex: 6, raw: true);

    $outputs = $response->getOutputs();

    expect($client)->toBeInstanceOf(Client::class);
});
