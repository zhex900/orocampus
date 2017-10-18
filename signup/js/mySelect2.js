// todo something wrong with jquery ($, console, and so on are all not defined)
// onmessage = function (e) {
//     "use strict";
//     console.log('Message received from main script club_registration');
//     console.log(e.data[0], ' | ', e.data[1]);
//     $.each(e.data[0], function (value, key) {
//         $(e.data[1]).select2({
//             data: [
//                 {
//                     id: key,
//                     text: value
//                 }
//             ]
//         });
//     });
// };

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
    });
}