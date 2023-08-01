<?php

it('can test', function () {
    $c = new \SergiX44\Gradio\Client('https://sanchit-gandhi-whisper-jax.hf.space/');

    expect($c)->toBeInstanceOf(\SergiX44\Gradio\Client::class);
    expect($c->getConfig())->toBeInstanceOf(\SergiX44\Gradio\DTO\Config::class);
});
