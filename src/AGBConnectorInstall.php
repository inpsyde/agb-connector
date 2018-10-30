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
     *
     * @since 1.0.0
     */
    public static function activate()
    {
        self::convertAgbConnectorPluginOptions();

        $userAuthToken = get_option('agb_connector_user_auth_token', '');
        if (! $userAuthToken) {
            $userAuthToken = md5(wp_generate_password(32, true, true));
            update_option('agb_connector_user_auth_token', $userAuthToken);
        }

        $textTypes = [
            'agb' => 0,
            'datenschutz' => 0,
            'widerruf' => 0,
            'impressum' => 0,
        ];

        $textTypesAllocation = get_option('agb_connector_text_types_allocation', []);
        $textTypesAllocation = array_merge($textTypes, $textTypesAllocation);
        update_option('agb_connector_text_types_allocation', $textTypesAllocation);
    }

    /**
     * Convert old Plugin data to new
     *
     * @since 1.0.0
     */
    public static function convertAgbConnectorPluginOptions()
    {
        $agbConnectorOptions = get_option('agb_connectors_settings', []);

        if (! $agbConnectorOptions) {
            return;
        }

        $textTypesAllocation = [];
        $wcEmailAppendPdf = [];

        $textTypesAllocation['agb'] = isset($agbConnectorOptions['agb_connector_agb_page']) ? absint($agbConnectorOptions['agb_connector_agb_page']) : 0;
        $textTypesAllocation['impressum'] = isset($agbConnectorOptions['agb_connector_impressum_page']) ? absint($agbConnectorOptions['agb_connector_impressum_page']) : 0;
        $textTypesAllocation['datenschutz'] = isset($agbConnectorOptions['agb_connector_datenschutz_page']) ? absint($agbConnectorOptions['agb_connector_datenschutz_page']) : 0;
        $textTypesAllocation['widerruf'] = isset($agbConnectorOptions['agb_connector_widerruf_page']) ? absint($agbConnectorOptions['agb_connector_widerruf_page']) : 0;

        if (! empty($agbConnectorOptions['agb_connector_api'])) {
            update_option('agb_connector_user_auth_token', $agbConnectorOptions['agb_connector_api']);
        }

        if (! empty($agbConnectorOptions['agb_connector_agb_pdf'])) {
            $wcEmailAppendPdf['datenschutz'] = true;
        };

        if (! empty($agbConnectorOptions['agb_connector_widerruf_pdf'])) {
            $wcEmailAppendPdf['widerruf'] = true;
        };

        $updatedWc = update_option('agb_connector_wc_append_email', $wcEmailAppendPdf);
        $updated = update_option('agb_connector_text_types_allocation', $textTypesAllocation);
        if ($updated && $updatedWc) {
            delete_option('agb_connectors_settings');
        }
    }
}
