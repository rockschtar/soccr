<?php

namespace ClubfansUnited\Blocks;

use Rockschtar\WordPress\Controller\HookController;
use WP_Block_Type;

abstract class Block
{
    use HookController;

    private ?WP_Block_Type $blockType = null;

    private function __construct()
    {
        $this->addAction('init', 'registerBlock');
        $this->addFilter('plugins_url', 'pluginsUrl', 10, 3);
    }

    abstract public function blockDirectory(): string;

    private function absBlockDirectory(): string
    {
        return CUE_ROOT_DIR . $this->blockDirectory();
    }

    protected function blockname(): string
    {
        return $this->blockType->name;
    }

    private function blockUrl(): string
    {
        return home_url(str_replace('/web', '', $this->blockDirectory()));
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
            $classes[] = $attributes['className'];
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
            $args['render_callback'] = $this->addCallback('render');
            $assets = include $this->absBlockDirectory() . DIRECTORY_SEPARATOR . 'index.asset.php';
            $handle = sanitize_key(strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', get_class($this))));

            wp_register_style(
                $handle,
                $this->blockUrl() . '/style-index.css',
                $assets['version']
            );

            $args['editor_style'] = $handle;
            $args['style'] = $handle;
        }

        $this->blockType = register_block_type($this->absBlockDirectory(), $args);
    }

    private function pluginsUrl(string $url, string $path, string $plugin): string
    {
        if (strpos($plugin, $this->absBlockDirectory()) !== false) {
            return home_url(str_replace('/web', '', $this->blockDirectory()) . '/' . $path);
        }

        return $url;
    }
}
