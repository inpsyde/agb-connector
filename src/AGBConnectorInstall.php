<?php # -*- coding: utf-8 -*-

/**
 * Class AGBConnectorInstall
 *
 * @since 1.0.0
 */
class AGBConnectorInstall
{

    /**
     * Initiate some things on activation
     */
    public static function install()
    {
        $userAuthToken = get_option(AGBConnectorKeysInterface::OPTION_USER_AUTH_TOKEN, '');
        if (! $userAuthToken) {
            $userAuthToken = md5(wp_generate_password(32, true, true));
            update_option(AGBConnectorKeysInterface::OPTION_USER_AUTH_TOKEN, $userAuthToken);
        }

        $textAllocations = get_option(AGBConnectorKeysInterface::OPTION_TEXT_ALLOCATIONS);
        if (false !== $textAllocations) {
            return;
        }

        add_option(AGBConnectorKeysInterface::OPTION_TEXT_ALLOCATIONS, []);

        self::convertOldAgbConnectorPluginOptions();
        self::update100_110();
    }

    /**
     * Update options from 1.0.0 to 1.1.0 version
     */
    public static function update100_110()
    {
        $textTypesAllocation = get_option('agb_connector_text_types_allocation', []);
        if (! $textTypesAllocation) {
            return;
        }

        $append_email = get_option('agb_connector_wc_append_email', []);
        $textAllocations = [];

        foreach ($textTypesAllocation as $type => $allocation) {
            if (is_array($allocation) || ! $allocation) {
                continue;
            }

            $textAllocations[$type][0] = [
                'country' => 'DE',
                'language' => 'de',
                'pageId' => (int)$allocation,
                'wcOrderConfirmationEmailAttachment' => ! empty($append_email[$type]),
            ];
        }

        delete_option('agb_connector_wc_append_email');
        delete_option('agb_connector_text_types_allocation');
        update_option(AGBConnectorKeysInterface::OPTION_TEXT_ALLOCATIONS, $textAllocations);
    }

    /**
     * Convert old Plugin data to new
     */
    public static function convertOldAgbConnectorPluginOptions()
    {
        $agbConnectorOptions = get_option('agb_connectors_settings', []);
        if (! $agbConnectorOptions) {
            return;
        }

        if (! empty($agbConnectorOptions['agb_connector_api'])) {
            update_option(AGBConnectorKeysInterface::OPTION_USER_AUTH_TOKEN, $agbConnectorOptions['agb_connector_api']);
        }

        $textAllocations = [];
        if (isset($agbConnectorOptions['agb_connector_agb_page'])) {
            $textAllocations['agb'][0] = [
                'country' => 'DE',
                'language' => 'de',
                'pageId' => absint($agbConnectorOptions['agb_connector_agb_page']),
                'wcOrderConfirmationEmailAttachment' => ! empty($agbConnectorOptions['agb_connector_agb_pdf']),
            ];
        }
        if (isset($agbConnectorOptions['agb_connector_impressum_page'])) {
            $textAllocations['impressum'][0] = [
                'country' => 'DE',
                'language' => 'de',
                'pageId' => absint($agbConnectorOptions['agb_connector_impressum_page']),
                'wcOrderConfirmationEmailAttachment' => false,
            ];
        }
        if (isset($agbConnectorOptions['agb_connector_agb_page'])) {
            $textAllocations['datenschutz'][0] = [
                'country' => 'DE',
                'language' => 'de',
                'pageId' => absint($agbConnectorOptions['agb_connector_datenschutz_page']),
                'wcOrderConfirmationEmailAttachment' => ! empty($agbConnectorOptions['agb_connector_datenschutz_pdf']),
            ];
        }
        if (isset($agbConnectorOptions['agb_connector_agb_page'])) {
            $textAllocations['widerruf'][0] = [
                'country' => 'DE',
                'language' => 'de',
                'pageId' => absint($agbConnectorOptions['agb_connector_widerruf_page']),
                'wcOrderConfirmationEmailAttachment' => ! empty($agbConnectorOptions['agb_connector_widerruf_pdf']),
            ];
        }

        $updated = update_option(AGBConnectorKeysInterface::OPTION_TEXT_ALLOCATIONS, $textAllocations);
        if ($updated) {
            delete_option('agb_connectors_settings');
        }
    }
}
