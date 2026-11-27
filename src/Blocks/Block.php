<?php

namespace Rockschtar\Soccr\Blocks;

use Rockschtar\Soccr\Traits\Singelton;
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

    final protected function attributionHtml(): string
    {
        $attribution = sprintf(
            /* translators: %s is a link to openligadb.de */
            __('Data: %s (ODbL)', 'soccr'),
            '<a href="https://www.openligadb.de" target="_blank" rel="noopener noreferrer">OpenLigaDB</a>',
        );

        return '<div class="wp-block-soccr-attribution">' . $attribution . '</div>';
    }

    final public function blockClasses(
        array $attributes = [],
        array $additionalClasses = [],
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
        }

        $this->blockType = register_block_type($this->absBlockDirectory(), $args);

        if ($this->blockType instanceof WP_Block_Type) {
            foreach ($this->blockType->editor_script_handles as $scriptHandle) {
                wp_set_script_translations($scriptHandle, 'soccr');
            }
        }
    }
}
