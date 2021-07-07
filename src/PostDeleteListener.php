<?php
declare(strict_types=1);

namespace Inpsyde\AGBConnector;

use Inpsyde\AGBConnector\Document\Map\WpPostMetaFields;

class PostDeleteListener
{
    public function init(): void
    {
        add_action('before_delete_post', function ($postId) {
            if (! metadata_exists('post', $postId, WpPostMetaFields::WP_POST_DOCUMENT_TYPE)) {
                return;
            }
            
            $attachments = get_attached_media('', $postId);

            foreach ($attachments as $attachment) {
                wp_delete_attachment($attachment->ID, 'true');
            }
        });
    }
}
