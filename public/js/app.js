$('select[name="edit_section_id"]').change(function() {
    $('input[name="edit_name"]').val($('select[name="edit_section_id"] option:selected').text());
});
