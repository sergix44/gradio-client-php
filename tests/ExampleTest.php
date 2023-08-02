<?php

it('can test', function () {
    $c = new \SergiX44\Gradio\Client('https://huggingface-projects-qr-code-ai-art-generator--85d7ps6wv.hf.space');

    expect($c)->toBeInstanceOf(\SergiX44\Gradio\Client::class);
    expect($c->getConfig())->toBeInstanceOf(\SergiX44\Gradio\DTO\Config::class);
});
