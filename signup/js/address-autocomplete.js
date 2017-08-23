/**
 * Author:      Jason
 * File name:   address_validation.html
 * Info:        Google API address auto-completion
 * Source:      https://developers.google.com/maps/documentation/javascript/examples/places-autocomplete-addressform
 *
 * Adds an autocomplete address field to html forms and uses the autocomplete feature of Google's Places API
 */

// the variable which will contain the element to be autocompleted
var autocomplete;

// array containing attributes value pairs where the attributes are the IDs of the HTML form inputs and the values
// indicate whether this field will be filled with the full name of a section of the address or the abbreviated version
// for example whether to write Western Australia (full name) or WA in when considering states
var componentForm = {
    street_number: 'short_name',
    route: 'long_name',                         // AKA street name
    locality: 'long_name',                      // AKA city
    administrative_area_level_1: 'long_name',   // AKA state
    country: 'long_name',
    postal_code: 'short_name'
};

function initialize() {
    // Create the autocomplete object, restricting the search
    // to geographical location types.
    autocomplete = new google.maps.places.Autocomplete(
        /** @type {HTMLInputElement} */(document.getElementById('address')),
        {types: ['geocode']});

    // When the user selects an address from the dropdown,
    // populate the address fields in the form.
    google.maps.event.addListener(autocomplete, 'place_changed', function() {
        fillInAddress();
    });

}

function fillInAddress() {
    // Get the place details from the autocomplete object.
    var place = autocomplete.getPlace();

    for (var component in componentForm) {
        document.getElementById(component).value = '';
    }

    // Get each component of the address from the place details
    // and fill the corresponding field on the form.
    for (var i = 0; i < place.address_components.length; i++) {
        var addressType = place.address_components[i].types[0];
        if (componentForm[addressType]) {
            var val = place.address_components[i][componentForm[addressType]];
            document.getElementById(addressType).value = val;
        }
    }
}

// Bias the autocomplete object to the user's geographical location,
// as supplied by the browser's 'navigator.geolocation' object.
function geolocate() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function (position) {
            var geolocation = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
            var circle = new google.maps.Circle({
                center: geolocation,
                radius: position.coords.accuracy
            });
            autocomplete.setBounds(circle.getBounds());
        });
    }
}