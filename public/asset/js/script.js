const citySelect = document.getElementById('outing_city');
const locationSelect = document.getElementById('outing_location');
const locationDetailsDiv = document.getElementById('locationDetails');
citySelect.addEventListener('change', getRelatedLocations);

async function getRelatedLocations(element) {
    const cityId = element.target.value;
    locationSelect.disabled = false;

    const response = await fetch('/sortirRED/public/api/city/' + cityId + '/getRelatedLocations');
    await response.json().then(function(response) {
        response.forEach((location) => {

            let defaultOption = document.createElement('option');
            defaultOption.id = '#';
            defaultOption.innerText = '-- Choisir un lieu --';
            defaultOption.selected = true;
            defaultOption.disabled = true;

            locationSelect.innerHTML = ''
            locationSelect.appendChild(defaultOption);

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
    const response = await fetch('/sortirRED/public/api/location/' + locationId);
    const location = await response.json();
    showLocationDetails(location);
}

function showLocationDetails(location) {

}