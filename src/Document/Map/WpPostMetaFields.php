<?php


namespace Inpsyde\AGBConnector\Document\Map;

/**
 * Class WpPostMetaFields
 *
 * @package Inpsyde\AGBConnector\Document\Map
 *
 * The list of post meta fields used to save document data.
 */
class WpPostMetaFields
{
    const WP_POST_DOCUMENT_TYPE = 'agbc_document_type';

    const WP_POST_DOCUMENT_LANGUAGE = 'agbc_document_language';

    const WP_POST_DOCUMENT_COUNTRY = 'agbc_document_country';

    const WP_POST_DOCUMENT_FLAG_ATTACH_TO_WC_EMAIL = 'agbc_flag_attach_to_wc_email';

    const WP_POST_DOCUMENT_FLAG_SAVE_PDF = 'agbc_flag_save_pdf';

    const WP_POST_DOCUMENT_FLAG_HIDE_TITLE = 'agbc_flag_hide_title';
}
