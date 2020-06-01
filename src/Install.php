<?php # -*- coding: utf-8 -*-

namespace Inpsyde\AGBConnector;

/**
 * Class Install
 */
class Install
{

    /**
     * Initiate some things on activation
     */
    public static function activate()
    {
        $userAuthToken = get_option(Plugin::OPTION_USER_AUTH_TOKEN, '');
        if (! $userAuthToken) {
            $userAuthToken = md5(wp_generate_password(32, true, true));
            update_option(Plugin::OPTION_USER_AUTH_TOKEN, $userAuthToken);
        }

        $textAllocations = get_option(Plugin::OPTION_TEXT_ALLOCATIONS);
        if (false !== $textAllocations) {
            return;
        }

        add_option(Plugin::OPTION_TEXT_ALLOCATIONS, []);

        self::convertOldAgbConnectorPluginOptions();
        self::update100To200();
    }

    /**
     * Update options from 1.0.0 to 2.0.0 version
     */
    public static function update100To200()
    {
        $textTypesAllocation = get_option('agb_connector_text_types_allocation', []);
        if (! $textTypesAllocation) {
            return;
        }

        $appendEmail = get_option('agb_connector_wc_append_email', []);
        $textAllocations = [];

        foreach ($textTypesAllocation as $type => $allocation) {
            if (is_array($allocation) || ! $allocation) {
                continue;
            }

            $textAllocations[$type][0] = [
                'country' => 'DE',
                'language' => 'de',
                'pageId' => (int)$allocation,
                'wcOrderEmailAttachment' => ! empty($appendEmail[$type]),
                'savePdfFile' => true,
            ];
        }

        delete_option('agb_connector_wc_append_email');
        delete_option('agb_connector_text_types_allocation');
        update_option(Plugin::OPTION_TEXT_ALLOCATIONS, $textAllocations);
    }

    /**
     * Convert old Plugin data to new
     *
     * phpcs:disable Inpsyde.CodeQuality.FunctionLength.TooLong
     */
    public static function convertOldAgbConnectorPluginOptions()
    {
        $agbConnectorOptions = get_option('agb_connectors_settings', []);
        if (! $agbConnectorOptions) {
            return;
        }

        if (! empty($agbConnectorOptions['agb_connector_api'])) {
            update_option(
                Plugin::OPTION_USER_AUTH_TOKEN,
                $agbConnectorOptions['agb_connector_api']
            );
        }

        $textAllocations = [];
        if (isset($agbConnectorOptions['agb_connector_agb_page'])) {
            $textAllocations['agb'][0] = [
                'country' => 'DE',
                'language' => 'de',
                'pageId' => absint($agbConnectorOptions['agb_connector_agb_page']),
                'wcOrderEmailAttachment' =>
                    ! empty($agbConnectorOptions['agb_connector_agb_pdf']),
                'savePdfFile' => true,
            ];
        }
        if (isset($agbConnectorOptions['agb_connector_impressum_page'])) {
            $textAllocations['impressum'][0] = [
                'country' => 'DE',
                'language' => 'de',
                'pageId' => absint($agbConnectorOptions['agb_connector_impressum_page']),
                'wcOrderEmailAttachment' => false,
            ];
        }
        if (isset($agbConnectorOptions['agb_connector_datenschutz_page'])) {
            $textAllocations['datenschutz'][0] = [
                'country' => 'DE',
                'language' => 'de',
                'pageId' => absint($agbConnectorOptions['agb_connector_datenschutz_page']),
                'wcOrderEmailAttachment' =>
                    ! empty($agbConnectorOptions['agb_connector_datenschutz_pdf']),
                'savePdfFile' => true,
            ];
        }
        if (isset($agbConnectorOptions['agb_connector_widerruf_page'])) {
            $textAllocations['widerruf'][0] = [
                'country' => 'DE',
                'language' => 'de',
                'pageId' => absint($agbConnectorOptions['agb_connector_widerruf_page']),
                'wcOrderEmailAttachment' =>
                    ! empty($agbConnectorOptions['agb_connector_widerruf_pdf']),
                'savePdfFile' => true,
            ];
        }

        $updated = update_option(Plugin::OPTION_TEXT_ALLOCATIONS, $textAllocations);
        if ($updated) {
            delete_option('agb_connectors_settings');
        }
    }
}
