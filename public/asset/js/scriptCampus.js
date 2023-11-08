const updateUrl = document.getElementById('js-url-data').dataset.urlModify
document.querySelectorAll("[id^='modify']").forEach((link)=>{
    link.addEventListener('click', function (evt){
        evt.preventDefault()
        let campus = prompt("Nom du campus:")
        document.getElementById('campus_name').value = campus
        let campusId = link.id.slice(6, link.id.length);
        const targetURL = updateUrl.replace('0', campusId);
        document.getElementsByName('campus')[0].action = targetURL;
        document.getElementById('campus_name').name = 'newName'
        document.getElementsByName('campus')[0].submit();
    })
})