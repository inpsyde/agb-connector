<?php # -*- coding: utf-8 -*-

/**
 * Interface AGBConnectorKeysInterface
 */
interface AGBConnectorKeysInterface
{
    /**
     * Option to store Text type allocation
     * Format: [
     *      'agb' => [
     *          0 => [
     *              'country' => 'DE',
     *              'language' => 'de',
     *              'pageId' => 15,
     *              'wcOrderConfirmationEmailAttachment' => true
     *          ]
     *      ]
     * ]
     */
    const OPTION_TEXT_ALLOCATIONS = 'agb_connector_text_allocations';

    /**
     * Option to store the auth token
     * Format: string
     */
    const OPTION_USER_AUTH_TOKEN = 'agb_connector_user_auth_token';
}
