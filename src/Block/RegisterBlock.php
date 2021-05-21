<?php
declare(strict_types=1);

namespace Inpsyde\AGBConnector\Block;

use Inpsyde\AGBConnector\Plugin;

/**
 * Register Gutenberg block for Document.
 */
class RegisterBlock
{
    /**
     * Register a new gutenberg block for Document.
     */
    public function __invoke(): void
    {
        $this->registerBlock();
        $this->registerBlockAssets();
    }

    /**
     * Register block type for a Document.
     */
    protected function registerBlock(): void
    {
        register_block_type( Plugin::DOCUMENT_BLOCK_TYPE, array(
            'editor_script' => 'agb-document-block-editor'
        ) );
    }

    /**
     * Register assets for the Document block.
     */
    protected function registerBlockAssets(): void
    {
        if ( ! function_exists( 'register_block_type' ) ) {
            return;
        }

        $plugin = agb_connector();

        $blockScriptUrl = plugin_dir_url($plugin->pluginFilePath()) .
            'assets/js/document-block/index.js';

        $blockScriptPath = plugin_dir_path($plugin->pluginFilePath()) .
            'assets/js/document-block/index.js';

        if(! file_exists($blockScriptPath)) {
            return;
        }


        wp_register_script(
            'agb-document-block-editor',
            $blockScriptUrl,
            array(
                'wp-blocks',
                'wp-i18n',
                'wp-element',
            ),
            filemtime( $blockScriptPath )
        );
    }
}
