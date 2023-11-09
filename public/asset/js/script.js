const citySelect = document.getElementById('outing_city');
const locationSelect = document.getElementById('outing_location');
const locationDetailsDiv = document.getElementById('locationDetails');
const btnLocationForm = document.getElementById('btnTriggerLocationForm');
btnLocationForm.disabled = true;
const eventChange = new Event('change');
citySelect.addEventListener('change', getRelatedLocations);
let updatedLocation = false;

/*
* These constants are responsible for getting a dynamic URL based on the project name in
* order to fetch the relevant data from the application endpoint.
 */
const cityXLocationsURL = document.getElementById('js-url-data').dataset.apiCityXLocations;
const locationDetailsURL = document.getElementById('js-url-data').dataset.apiLocationDetails;
const locationAddURL = document.getElementById('js-url-data').dataset.apiLocationAdd;

/*
* This method calls asynchronously a fetch to the database in order to get the locations related to a
* specific city (the target of the select).
* It is triggered by an onChange event on the select input dedicated to the city.
 */
async function getRelatedLocations(element) {

    const cityId = element.target.value;
    locationSelect.disabled = false;
    btnLocationForm.disabled = cityId === '';

    locationDetailsDiv.innerHTML = "";

    let defaultOption = document.createElement('option');
    defaultOption.id = '#';
    defaultOption.innerText = '-- Choisir un lieu --';
    defaultOption.selected = true;
    defaultOption.disabled = true;

    locationSelect.innerHTML = ''
    locationSelect.appendChild(defaultOption);

    const fetchLocationsURL = cityXLocationsURL.replace('0', cityId);
    const response = await fetch(fetchLocationsURL);
    await response.json().then((response) => {
        response.forEach((location) => {
            let option = document.createElement('option');
            option.value = location.id;
            option.innerText = location.name;
            locationSelect.appendChild(option);
        })
    });

    if (updatedLocation) {
        locationSelect.lastElementChild.selected = true;
        updatedLocation = false;
        locationSelect.dispatchEvent(eventChange)
    }

}

locationSelect.addEventListener('change', getLocationDetails);

/*
* This method calls asynchronously a fetch to the database in order to get the details of a location in the database.
* It is triggered by an onChange event on the select input dedicated to the location.
 */
async function getLocationDetails(element) {

    const locationId = element.target.value;
    locationDetailsDiv.innerHTML = "";

    const fetchLocationDetailsURL = locationDetailsURL.replace('0', locationId);
    const response = await fetch(fetchLocationDetailsURL);
    await response.json().then((response) => {
        response.forEach((attribute) => {
            let streetP = document.createElement('p');
            streetP.innerText = 'Rue : ' + attribute.street;

            let postcodeP = document.createElement('p');
            postcodeP.innerText = 'Code postal : ' + attribute.city.postcode;

            locationDetailsDiv.appendChild(streetP);
            locationDetailsDiv.appendChild(postcodeP);

            if (!attribute.latitude) {
                getLocationLatAndLongitude(attribute.street, attribute.city.postcode);
            } else {
                let latitudeP = document.createElement('p');
                latitudeP.innerText = 'Latitude : ' + attribute.latitude;

                let longitudeP = document.createElement('p');
                longitudeP.innerText = 'Latitude : ' + attribute.longitude;

                locationDetailsDiv.appendChild(latitudeP);
                locationDetailsDiv.appendChild(longitudeP);
            }

        })
    });

}

async function getLocationLatAndLongitude(street, postcode) {

    const api_url = 'https://api-adresse.data.gouv.fr/search/?q=';
    let coordinates = [];
    const response = await fetch(api_url + formatStreet(street) + '&postcode=' + formatPostcode(postcode));
    await response.json().then((data) => {
        let latitudeP = document.createElement('p');
        let longitudeP = document.createElement('p');
        latitudeP.innerText = 'Latitude : ' + data['features'][0].geometry.coordinates[0];
        longitudeP.innerText = 'Longitude : ' + data['features'][0].geometry.coordinates[1];
        locationDetailsDiv.appendChild(latitudeP);
        locationDetailsDiv.appendChild(longitudeP);
    })

    return coordinates;

}

// Formatting a location street to match the API for latitude and longitude requirements
function formatStreet(street) {
    let streetArray = street.split(' ');
    let newStreetArray = []
    streetArray.map((item) => {
        newStreetArray.push(item.replace(',', '').replace('\'', ''));
    });
    return newStreetArray.join('+');
}

// Formatting a city postcode to match the API for latitude and longitude requirements
function formatPostcode(postcode) {
    return postcode.replace(' ', '');
}

const createLocBtn = document.getElementById('createLocation');
const inputNameLoc = document.getElementById('locName');
const inputStreetLoc = document.getElementById('locStreet');
const inputCityLoc = document.getElementById('locCity');
const inputLatitudeLoc = document.getElementById('locLat');
const inputLongitudeLoc = document.getElementById('locLong');

createLocBtn.addEventListener('click', function () {
    inputCityLoc.value = document.getElementById('outing_city').value;
    inputLatitudeLoc.value = 1.5;
    inputLongitudeLoc.value = -1.5;

    let location = JSON.stringify({
        name: inputNameLoc.value,
        street: inputStreetLoc.value,
        city: document.getElementById('outing_city').value,
        latitude: 1.5,
        longitude: -1.5
    });

    fetch(locationAddURL, {
        method: 'POST',
        body: location,
        headers: {
            'Content-type': 'application/json; charset=UTF-8'
        }
    }).then(() => {
        updatedLocation = true;

        citySelect.dispatchEvent(eventChange);
    });

})