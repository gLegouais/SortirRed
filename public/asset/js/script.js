const citySelect = document.getElementById('outing_city');
const locationXCityData = document.getElementsByClassName('locationsData');
const locationDiv = document.getElementById('location');
const locationForm = document.getElementById('locationForm');
const locationNameInput = document.getElementById('outing_location_name');
const locationStreetInput = document.getElementById('outing_location_street');
locationForm.style.display = 'none';

citySelect.addEventListener('change', function () {
    let city = this.value;
    if (document.getElementById('locationSelect')) {
        locationDiv.removeChild(document.getElementById('locationSelectLabel'));
        locationDiv.removeChild(document.getElementById('locationSelect'));
        locationDiv.removeChild(document.getElementById('locationDetailDiv'));
        locationDiv.removeChild(document.getElementById('addLocation'));
    }

    const locationSelectLabel = document.createElement('label');
    locationSelectLabel.for = 'locationSelect';
    locationSelectLabel.innerText = 'Lieu';
    locationSelectLabel.id = 'locationSelectLabel';
    locationDiv.appendChild(locationSelectLabel);

    const locationSelect = document.createElement('select');
    locationSelect.id = 'locationSelect';
    locationSelect.name = 'locationSelect';
    locationSelect.className = 'form-select';

    const addLocation = document.createElement('button');
    addLocation.type = 'button';
    addLocation.dataToggle = 'modal';
    addLocation.dataTarget = '#exampleModalCenter'
    addLocation.innerHTML = '<i class="bi bi-plus-circle"></i>';
    addLocation.id = 'addLocation';
    addLocation.className = 'btn btn-secondary mt-2';

    addLocation.addEventListener('click', function () {

        locationForm.style.display = 'block';
        locationSelect.disabled = true;
        if (document.getElementById('locationDetailDiv')) {
            document.getElementById('locationDetailDiv').innerHTML = "";
            locationDiv.removeChild(document.getElementById('locationSelectLabel'));
            locationDiv.removeChild(document.getElementById('locationSelect'));
            locationDiv.removeChild(document.getElementById('locationDetailDiv'));
            locationDiv.removeChild(document.getElementById('addLocation'));
        }

    });

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
    if (!document.getElementById('addLocation')) {
        locationDiv.appendChild(addLocation);
    }

    let locationDetailDiv = document.createElement('div');
    locationDetailDiv.id = 'locationDetailDiv';
    locationDetailDiv.className = 'mt-3'
    locationDiv.appendChild(locationDetailDiv);
    locationSelect.addEventListener('change', function () {
        let locationId = locationSelect.value;
        for (let loc of locationsArray) {
            if (loc.id === locationId) {
                locationDetailDiv.innerHTML = '';
                locationNameInput.value = loc.name;

                let street = document.createElement('p');
                street.innerText = 'Rue : ' + loc.street;
                locationStreetInput.value = loc.street;
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