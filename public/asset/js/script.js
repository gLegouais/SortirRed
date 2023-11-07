const citySelect = document.getElementById('outing_city');
const locationSelect = document.getElementById('outing_location');
const locationDetailsDiv = document.getElementById('locationDetails');
const eventChange = new Event('change');
citySelect.addEventListener('change', getRelatedLocations);
let updatedLocation = false;

const cityXLocationsURL = document.getElementById('js-url-data').dataset.cityXLocations;
const locationDetailsURL = document.getElementById('js-url-data').dataset.locationDetails;
const locationAddURL = document.getElementById('js-url-data').dataset.locationAdd;

console.log(cityXLocationsURL);
console.log(locationDetailsURL);
console.log(locationAddURL);

async function getRelatedLocations(element) {
    const cityId = element.target.value;
    locationSelect.disabled = false;
    locationDetailsDiv.innerHTML = "";
    let defaultOption = document.createElement('option');
    defaultOption.id = '#';
    defaultOption.innerText = '-- Choisir un lieu --';
    defaultOption.selected = true;
    defaultOption.disabled = true;
    locationSelect.innerHTML = ''
    locationSelect.appendChild(defaultOption);

    const response = await fetch('/sortirRED/public/api/city/' + cityId + '/getRelatedLocations');
    await response.json().then((response) => {
        response.forEach((location) => {
            let option = document.createElement('option');
            option.value = location.id;
            option.innerText = location.name;
            locationSelect.appendChild(option);
        })
    });
    if(updatedLocation) {
        console.log('updated !!!')
        locationSelect.lastElementChild.selected = true;
        updatedLocation = false;
        locationSelect.dispatchEvent(eventChange)
    }
}

locationSelect.addEventListener('change', getLocationDetails);

async function getLocationDetails(element) {
    const locationId = element.target.value;
    locationDetailsDiv.innerHTML = "";
    const response = await fetch('/sortirRED/public/api/location/' + locationId);
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
    console.log(api_url + formatStreet(street) + '&postcode=' + postcode);
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

function formatStreet(street) {
    let streetArray = street.split(' ');
    let newStreetArray = []
    streetArray.map((item) => {
        newStreetArray.push(item.replace(',', '').replace('\'', ''));
    });
    return newStreetArray.join('+');
}

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

    fetch('/sortirRED/public/api/location/create', {
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