<?php
declare(strict_types=1);

namespace Inpsyde\AGBConnector\Document\View;

use Inpsyde\AGBConnector\Document\DocumentInterface;
use Inpsyde\AGBConnector\Document\Repository\DocumentRepositoryInterface;
use WP_Post;

/**
 * Remove page title if it displays a document with enabled option 'Hide page title'.
 *
 * If more than one document is displayed on the page, then remove the title only if all of
 * these documents have 'Hide page title' option enabled.
 */
class RemoveDocumentPageTitleIfEnabled
{
    /**
     * @var DocumentRepositoryInterface
     */
    protected $documentRepository;

    public function __construct(DocumentRepositoryInterface $documentRepository)
    {
        $this->documentRepository = $documentRepository;
    }

    public function __invoke(): void
    {
        add_action('the_post', function ($post) {

            $documentsDisplayedOnThePage = $this->getDocumentsDisplayedOnThePage($post);
            if (! $documentsDisplayedOnThePage || ! is_page()) {
                return;
            }

            if ($this->allDocumentsHaveHideTitleEnabled($documentsDisplayedOnThePage)) {
                add_filter('the_title', function ($originalTitle, $postId) use ($post) {
                    if ((int) $postId === (int) $post->ID) {
                        return '';
                    }

                    return $originalTitle;
                }, 10, 2);
            }
        });
    }

    /**
     * @param WP_Post $post
     *
     * @return DocumentInterface[]
     */
    protected function getDocumentsDisplayedOnThePage($post): array
    {
        $documentsIds = get_post_meta($post->ID, 'agb_page_contain_documents', true);

        if (! $documentsIds || ! is_array($documentsIds)) {
            return [];
        }

        return array_filter(array_map([$this->documentRepository, 'getDocumentById'], $documentsIds));
    }

    /**
     * Check if all of given documents have Hide Title option enabled.
     *
     * @param DocumentInterface[] $documents
     *
     * @return bool
     */
    protected function allDocumentsHaveHideTitleEnabled(array $documents): bool
    {
        foreach ($documents as $document) {
            if (! $document->getSettings()->getHideTitle()) {
                return false;
            }
        }

        return true;
    }
}
