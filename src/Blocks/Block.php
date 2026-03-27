<?php

namespace Rockschtar\WordPress\Soccr\Blocks;

use Rockschtar\WordPress\Soccr\Traits\Singelton;
use WP_Block_Type;

abstract class Block
{
    use Singelton;

    private WP_Block_Type|false|null $blockType = null;

    private function __construct()
    {
        add_action('init', $this->registerBlock(...));
    }

    abstract public function blockDirectory(): string;

    private function absBlockDirectory(): string
    {
        return rtrim(SOCCR_PLUGIN_DIR, '/') . '/' . ltrim($this->blockDirectory(), '/');
    }

    protected function blockname(): string
    {
        return $this->blockType instanceof WP_Block_Type ? $this->blockType->name : '';
    }

    private function distUrl(): string
    {
        return rtrim(SOCCR_PLUGIN_URL, '/') . $this->blockDirectory();
    }

    final protected function esc(mixed $value): string
    {
        return esc_html((string) $value);
    }

    final protected function attributionHtml(): string
    {
        return '<div class="wp-block-soccr-attribution">Daten: <a href="https://www.openligadb.de" target="_blank" rel="noopener noreferrer">OpenLigaDB</a> (ODbL)</div>';
    }

    final public function blockClasses(
        array $attributes = [],
        array $additionalClasses = []
    ): string {
        $classes = [$this->blockClass()];

        if (isset($attributes['align']) && $attributes['align']) {
            $classes[] = 'align' . $attributes['align'];
        }

        if (isset($attributes['className']) && $attributes['className']) {
            $classes[] = sanitize_html_class($attributes['className']);
        }

        foreach ($additionalClasses as $class) {
            $classes[] = $this->blockClass() . '-' . $class;
        }

        return implode(' ', $classes);
    }

    final public function blockClass(string $suffix = ''): string
    {
        $blockName = preg_replace('/[^A-Za-z0-9 ]/', '-', $this->blockName());
        $class = 'wp-block-' . $blockName;

        if (!empty($suffix)) {
            $class .= '-' . $suffix;
        }

        return $class;
    }

    private function registerBlock(): void
    {
        $args = [];
        if (method_exists($this, 'render')) {
            $args['render_callback'] = $this->render(...);
            $assets = include $this->absBlockDirectory() . DIRECTORY_SEPARATOR . 'index.asset.php';
            $handle = sanitize_key(strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', get_class($this))));


            wp_register_style(
                $handle,
                $this->distUrl() . '/style-index.css',
                [],
                $assets['version']
            );

            $args['editor_style'] = $handle;
            $args['style'] = $handle;
        }

        $this->blockType = register_block_type($this->absBlockDirectory(), $args);
    }

}
