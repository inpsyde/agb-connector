jQuery(document).ready(function($){
    $('table td input[type="checkbox"].agb-document-settings').on('change', function(e){
        let documentId = parseInt( $( $(e.target).closest('tr')).attr('id').substr(5) );
        let fieldName = $(e.target).attr('name');
        let value = $(e.target).is(':checked') ? '1' : '0';

        $.post(
            {
                url: ajaxurl,
                data: {
                    action: agbConnectorSettings.action,
                    documentId: documentId,
                    nonce: agbConnectorSettings.nonce,
                    fieldName: fieldName,
                    value: value
                },
                dataType: 'json',
                onSuccess: function (response) {
                    agbConnectorSettings.nonce = response.nonce;
                }
            }
        );
    });
});
