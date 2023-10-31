document.addEventListener('DOMContentLoaded', init);

function init() {
    console.log('Oki doki');
    const data = document.getElementById('js-data').dataset.citiesLocations;

    console.log(data);
}