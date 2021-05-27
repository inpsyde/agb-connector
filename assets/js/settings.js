jQuery(document).ready(function($){
    $('table td input[type="checkbox"].agb-document-settings').on('change', function(e){
        let documentId = parseInt( $( $(e.target).closest('tr')).attr('id').substr(5) );
        let fieldName = $(e.target).attr('name');
        let fieldValue = $(e.target).is(':checked') ? '1' : '0';

        if(fieldName === 'store_pdf')
        {
            let $attachPdfCheckbox = $(e.target).closest('tr').find('input[name="attach_pdf_to_wc"]');
            if(fieldValue === '1'){
                $attachPdfCheckbox.removeAttr('disabled');
            }
            else{
                $attachPdfCheckbox.attr('disabled', 'disabled').prop('checked', false);
            }
        }

        $.post(
            {
                url: ajaxurl,
                data: {
                    action: agbConnectorSettings.action,
                    documentId: documentId,
                    nonce: agbConnectorSettings.nonce,
                    fieldName: fieldName,
                    fieldValue: fieldValue
                },
                dataType: 'json',
                onSuccess: function (response) {
                    agbConnectorSettings.nonce = response.nonce;
                }
            }
        );
    });
});
