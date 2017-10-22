function mySelect2(json,field) {
    console.log('myselect called');
    $.each(json, function(key,value) {
        $(field).select2({
            data: [
                {
                    id: value,
                    text: key
                }
            ]
        });
        console.log('item fetched');
    });
}