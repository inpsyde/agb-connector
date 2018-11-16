function removePages() {
    jQuery('.remove').on('click', function () {
        jQuery(this).parent().parent().remove();
        return false;
    });
}
removePages();