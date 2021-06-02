<?php
declare(strict_types=1);

namespace Inpsyde\AGBConnector\Document\View;

/**
 * Service registering frontend styles for the document.
 */
class RegisterDocumentStyles
{
    /**
     * Path to the main plugin file.
     *
     * @var string
     */
    protected $mainPluginFilePath;

    /**
     * @param string $mainPluginFilePath
     */
    public function __construct(string $mainPluginFilePath)
    {
        $this->mainPluginFilePath = $mainPluginFilePath;
    }

    /**
     * Register css for documents.
     */
    public function __invoke(): void
    {
        add_action('wp_enqueue_scripts', function (): void {
            $cssLocalPath = 'assets/css/document-style.css';
            $pluginFileUrl = plugin_dir_url($this->mainPluginFilePath);
            $cssFullPath = plugin_dir_path($this->mainPluginFilePath) . $cssLocalPath;

            wp_enqueue_style(
                'agbc-document-styles',
                $pluginFileUrl . $cssLocalPath,
                [],
                file_exists($cssFullPath) ? filemtime($cssFullPath): 0
            );
        });
    }
}
