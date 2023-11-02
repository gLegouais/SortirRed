const citySelect = document.getElementById('outing_city');
const locationXCityData = document.getElementsByClassName('locationsData');
const locationDiv = document.getElementById('location');

citySelect.addEventListener('change', function () {
    let city = this.value;
    if (document.getElementById('locationSelect')) {
        locationDiv.removeChild(document.getElementById('locationSelectLabel'));
        locationDiv.removeChild(document.getElementById('locationSelect'));
        locationDiv.removeChild(document.getElementById('locationDetailDiv'));
    }

    const locationSelectLabel = document.createElement('label');
    locationSelectLabel.for = 'locationSelect';
    locationSelectLabel.innerText = 'Lieu';
    locationSelectLabel.id = 'locationSelectLabel';
    locationDiv.appendChild(locationSelectLabel);

    const locationSelect = document.createElement('select');
    locationSelect.id = 'locationSelect';
    locationSelect.name = 'locationSelect';

    let locationsArray = genParsedLocationData();
    let zipcode = '';
    for (let loc of locationsArray) {
        if (loc.cityId === city) {
            zipcode = loc.cityPostcode;
            let option = document.createElement('option');
            option.value = loc.id;
            option.id = loc.id;
            option.innerText = loc.name;
            locationSelect.appendChild(option);
        }
    }
    if (locationSelect.length === 0) {
        let option = document.createElement('option');
        option.value = '#';
        option.innerText = '-- Pas de lieu pour cette ville --';
        option.disabled = true;
        option.selected = true;
        locationSelect.appendChild(option);
    } else {
        let option = document.createElement('option');
        option.value = '#';
        option.innerText = '-- Choisir un lieu --';
        option.disabled = true;
        option.selected = true;
        locationSelect.insertAdjacentElement('afterbegin', option);
    }
    locationDiv.appendChild(locationSelect);
    let locationDetailDiv = document.createElement('div');
    locationDetailDiv.id = 'locationDetailDiv';
    locationDiv.appendChild(locationDetailDiv);
    locationSelect.addEventListener('change', function () {
        let locationId = locationSelect.value;
        for (let loc of locationsArray) {
            if (loc.id === locationId) {
                locationDetailDiv.innerHTML = '';

                let street = document.createElement('p');
                street.innerText = 'Rue : ' + loc.street;
                locationDetailDiv.appendChild(street);

                let postcode = document.createElement('p');
                postcode.innerText = 'Code postal : ' + zipcode;
                locationDetailDiv.appendChild(postcode);

                let latitude = document.createElement('p');
                latitude.innerText = 'Latitude : ' + loc.latitude;
                locationDetailDiv.appendChild(latitude);

                let longitude = document.createElement('p');
                longitude.innerText = 'Longitude : ' + loc.longitude;
                locationDetailDiv.appendChild(longitude);
            }
        }
    })
})

function genParsedLocationData() {
    let parsedLocationData = [];
    for (let i = 0; i < locationXCityData.length; i++) {
        let loc = locationXCityData.item(i);
        let location = {
            'cityName': loc.dataset.locationCityName,
            'cityId': loc.dataset.locationCityId,
            'cityPostcode': loc.dataset.locationCityPostcode,
            'id': loc.dataset.locationId,
            'name': loc.dataset.locationName,
            'street': loc.dataset.locationStreet,
            'latitude': loc.dataset.locationLatitude,
            'longitude': loc.dataset.locationLongitude,
        }
        parsedLocationData.push(location);
    }
    return parsedLocationData;
}