function mySelect2(file,field) {
    $.getJSON(file, function(obj) {
        $.each(obj, function(key, value) {
            $(field).select2({
                data: [
                    {
                        id: value,
                        text: key
                    }
                ]
            });
        });
    });
}