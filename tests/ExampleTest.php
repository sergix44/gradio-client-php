<?php

use SergiX44\Gradio\Client;

it('can test', function () {
    $client = new Client('https://multimodalart-stable-cascade.hf.space');

    $response = $client->predict([
        "house", // string  in 'Prompt' Textbox component
        "!", // string  in 'Negative prompt' Textbox component
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
    $client = new Client('https://ysharma-explore-llamav2-with-tgi.hf.space/--replicas/brc3o/');

    $response = $client->predict([
        'list all names of the week in all languages', // str  in 'parameter_28' Textbox component
        '', // str  in 'Optional system prompt' Textbox component
        0.9, // float (numeric value between 0.0 and 1.0) in 'Temperature' Slider component
        4096, // float (numeric value between 0 and 4096) in 'Max new tokens' Slider component
        0.6, // float (numeric value between 0.0 and 1) in 'Top-p (nucleus sampling)' Slider component
        1.2, // float (numeric value between 1.0 and 2.0) in 'Repetition penalty' Slider component
    ], '/chat');

    $outputs = $response->getOutputs();

    expect($client)->toBeInstanceOf(Client::class);
});
