jQuery(function ($) {
    let _token = $('input[name="blast_options[token]"]').val()

    let _phones = $('.select-phones').select2({
            tags: true,
            tokenSeparators: [',', ' ']
        })

    let _groups = $('.select-groups').select2().prop('disabled', true)

    $.ajax({
        url: "https://blast.my/api/v1/groups",
        type: 'GET',
        headers: {
          "Authorization": "Bearer " + _token
        },
        dataType: 'json',
        success: function(res) {
            groups = res

            if(groups.length > 0) {
                $('#blast-groups').hide()

                _groups.prop('disabled',false)
                    .select2({
                        allowClear: true,
                        placeholder: '',
                        data: groups
                    })
                    .on("select2:clear", function(e) {
                        _phones.select2('destroy')
                            .empty()
                            .select2({
                                tags: true,
                                tokenSeparators: [',', ' ']
                            })
                    })
                    .on("select2:select", function(e) {
                        let selected = $(".select-groups option:selected").val();

                        $.ajax({
                            url: "https://blast.my/api/v1/groups/"+ selected,
                            type: 'GET',
                            headers: {
                                "Authorization": "Bearer " + _token
                            },
                            dataType: 'json',
                            success: function(res) {
                                phones = res

                                _phones.select2('destroy')
                                    .empty()
                                    .select2({
                                        data: phones
                                    })
                                    .select2('destroy')
                                    .find('option')
                                    .prop('selected', 'selected')
                                    .end()
                                    .select2({
                                        placeholder: '',
                                        tags: true,
                                        tokenSeparators: [',', ' ']
                                    })
                            }
                        })
                    })
            }
        }
    })

    $.ajax({
        url: "https://blast.my/api/v1/filter",
        type: 'GET',
        dataType: 'json',
        success: function(res) {
            let filters = res
            $('#text-output').show();
            $('#text-source').on('keyup keydown change', function() {
                let message = $(this).val()
                message = message.replace(new RegExp(filters.join('|'), 'g'), '***')
                $('#text-messages').text( message )
            })

            $('#blast-connect').show()
            $('#blast-disconnect').hide()
        }
    });
});