<?php

it('can test', function () {
    $c = new \SergiX44\Gradio\Client('https://nota-ai-compressed-stable-diffusion.hf.space/');

    $r = $c->predict([
        "banana and lemon", "", 7.5, 25, 1234,
    ], fnIndex: 4);

    print_r($r);
});
