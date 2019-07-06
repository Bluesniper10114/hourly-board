$('form').on('submit', function(){
    $(this).find(':submit').prop('disabled', true);
});
