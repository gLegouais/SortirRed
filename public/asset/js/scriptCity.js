const updateUrl = document.getElementById('js-url-data').dataset.urlModify
document.querySelectorAll("[id^='modify']").forEach((link)=>{
    link.addEventListener('click', function (evt){
        evt.preventDefault()
        let cityName = prompt("Nom de la ville:")
        document.getElementById('city_name').value = cityName
        let cityPostCode = prompt('Code postal de la ville')
        document.getElementById('city_postcode').value = cityPostCode
        let cityId = link.id.slice(6, link.id.length);
        const targetURL = updateUrl.replace('0', cityId);
        document.getElementsByName('city')[0].action = targetURL;
        document.getElementById('city_name').name = 'newName'
        document.getElementById('city_postcode').name = 'newPostCode'
        document.getElementsByName('city')[0].submit();
    })
})