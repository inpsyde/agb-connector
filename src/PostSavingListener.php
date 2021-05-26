<?php
declare(strict_types=1);

namespace Inpsyde\AGBConnector;

use Inpsyde\AGBConnector\Document\Map\WpPostMetaFields;
use Inpsyde\AGBConnector\Document\Repository\DocumentRepositoryInterface;
use WP_Post;

/**
 * Service listening for the post saving hook and adding a mark (meta field) to the post if it
 * displays Document (contains shortcode or block with the Document).
 */
class PostSavingListener
{
    /**
     * @var ShortCodes
     */
    protected $shortcodes;
    /**
     * @var DocumentRepositoryInterface
     */
    protected $documentRepository;

    /**
     * @param DocumentRepositoryInterface $documentRepository
     * @param ShortCodes $shortCodes
     */
    public function __construct(DocumentRepositoryInterface $documentRepository, ShortCodes $shortCodes)
    {
        $this->shortcodes = $shortCodes;
        $this->documentRepository = $documentRepository;
    }

    public function init(): void
    {
        add_action('save_post', [$this, 'handlePostSaving'], 10, 2);
    }

    /**
     * Add meta field to the saved post if it displays one of the Documents.
     *
     * @param int $postId
     * @param WP_Post$post
     */
    public function handlePostSaving($postId, $post): void
    {
        if(get_post_meta($postId, WpPostMetaFields::WP_POST_DOCUMENT_TYPE)){
            //it's a document itself, so it cannot be displaying page
            return;
        }

        $documentIdsFromShortcodes = $this->findDocumentsIdsUsedInShortcodes($post->post_content);

        $postDisplaysAgbDocument = $this->findDocumentsIdsUsedInShortcodes($post->post_content) ||
            $this->postHasDocumentBlocks($post);

        $this->updateAgbMetaField($postId, $postDisplaysAgbDocument);

    }

    /**
     * Check if provided text contains at least one of the plugin's shortcodes.
     *
     * @param string $text
     *
     * @return int[]
     */
    protected function findDocumentsIdsUsedInShortcodes(string $text): array
    {
        $foundDocumentIds = [];

        $shortcodeRegex = get_shortcode_regex($this->shortcodes->getShortcodeTags());

        preg_match_all('/' . $shortcodeRegex . '/', $text , $matches);

        $foundShortcodes = $matches[0] ?? [];
        $foundShortcodeTags = $matches[2] ?? [];
        $foundCount = count($foundShortcodes);

        for ($i = 0; $i < $foundCount; $i++){
            $atts = shortcode_parse_atts($foundShortcodes[$i]);
            $id = (int) $atts['id'] ?? 0;
            $country = $atts['country'] ?? '';
            $language = $atts['language'] ?? '';
            $documentType = $this->shortcodes->getDocumentTypeByShortcodeTag($foundShortcodeTags[$i]);

            $foundDocumentIds[] = $this->documentRepository->getDocumentPostIdByTypeCountryAndLanguage($documentType, $country, $language);
        }

        return $foundDocumentIds;
    }

    /**
     * Check if provided post contains at least one Document in form of Gutenberg block.
     *
     * @param WP_Post $post
     *
     * @return bool
     */
    protected function postHasDocumentBlocks(WP_Post $post): bool
    {
        return has_block('agb-document', $post);
    }

    /**
     * Add meta field if post displays Document, remove this meta field otherwise.
     *
     * @param int $postId
     * @param bool $hasAgb
     *
     * @return void
     */
    protected function updateAgbMetaField(int $postId, bool $hasAgb): void
    {
        if($hasAgb) {
            update_post_meta($postId, 'agb_page_contain_documents', true);

            return;
        }

        delete_post_meta($postId, 'agb_page_contain_documents');
    }
}
