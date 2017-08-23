$(function () {

    $.get('../data/zurmo.json', function (data) {
        var course = Object.keys(data['Course']).map(function (k) {
            return data['Course'][k]
        });
        var country = Object.keys(data['Country']).map(function (k) {
            return data['Country'][k]
        });
        //console.log(arguments.callee.caller.toString());
        $('#course_autocomplete').autocomplete({
            lookup: course,
            onSelect: function (suggestion) {
                //console.log('You selected: ' + suggestion.value);
            }
        });
        $('#country_autocomplete').autocomplete({
            lookup: country,
            onSelect: function (suggestion) {
               // console.log('You selected: ' + suggestion.value);
            }
        });
    });
});