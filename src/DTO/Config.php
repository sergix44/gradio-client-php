<?php

namespace SergiX44\Gradio\DTO;

class Config
{
    public ?string $version = null;
    public ?string $mode = null;
    public ?bool $dev_mode = false;
    public ?bool $analytics_enabled = false;

    public array $components = [];

    public ?string $css = null;
    public ?string $title = null;
    public bool $is_space = false;
    public bool $enable_queue = false;
    public bool $show_error = false;
    public bool $show_api = false;
    public bool $is_colab = false;
    public array $stylesheets = [];
    public array $dependencies = [];
    public ?string $root = null;

    public function fnIndexFromApiName(string $apiName): ?int
    {
        foreach ($this->dependencies as $index => $dep) {
            if ($dep->api_name === $apiName) {
                return $index;
            }
        }

        return null;
    }
}
