function mySelect2(json,field) {
    $.each(json, function(key,value) {
        $(field).select2({
            data: [
                {
                    id: value,
                    text: key
                }
            ]
        });
    });
}