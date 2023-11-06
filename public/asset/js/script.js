const citySelect = document.getElementById('outing_city');
const locationSelect = document.getElementById('outing_location');
const locationDetailsDiv = document.getElementById('locationDetails');
citySelect.addEventListener('change', getRelatedLocations);

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
    console.log(coordinates)
    return coordinates;
}
function formatStreet(street) {
    let streetArray = street.split(' ');
    let newStreetArray = []
    streetArray.map((item) => {
        newStreetArray.push(item.replace(',', ''));
    });
    return newStreetArray.join('+');
}
function formatPostcode(postcode) {
    return postcode.replace(' ', '');
}