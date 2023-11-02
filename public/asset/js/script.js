document.addEventListener('DOMContentLoaded', init);

function init() {
    console.log('Oki doki');
    const data = JSON.parse(document.getElementById('js-data').dataset.citiesLocations);

    console.log(data);
}