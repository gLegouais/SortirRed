document.querySelectorAll('.desk-icon').forEach((link) => {
    link.addEventListener('click', function(event) {
        event.preventDefault();
        document.querySelectorAll('.desk-icon').forEach((icon) => {
            icon.classList.remove('desk-icon-active');
            icon.querySelector('a').classList.remove('desk-link-active');
        })
        link.classList.add('desk-icon-active');
        link.querySelector('a').classList.add('desk-link-active');

    })
    link.addEventListener('dblclick', function() {
        window.location = this.querySelector('a').href;
    })
})

document.getElementsByTagName('main')[0].addEventListener('click', function (event) {
    event.stopPropagation();
    document.querySelectorAll('.desk-icon').forEach((icon) => {
        if (icon.classList.contains('desk-icon-active')) {
            icon.classList.remove('desk-icon-active');
            icon.querySelector('a').classList.remove('desk-link-active');
        }
    })
})