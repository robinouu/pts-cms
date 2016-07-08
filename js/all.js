$(document).ready(function (){
    $.datetimepicker.setLocale('fr');

    $('.editor').each(function (){
        console.log('editor');
        CKEDITOR.replace($(this).attr('id'));
    });
    $('input[type=datetime]').each(function (){
        $(this).datetimepicker({format: 'd/m/Y H:i:s'});
    });

});
