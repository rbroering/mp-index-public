/* Dropdown */
const navToggles_Dropdown_Main = document.querySelector('header .nav-toggle.nav-action--dropdown');
const navToggles_Dropdown = document.querySelectorAll('.nav-toggle.nav-action--dropdown');
const navDropdown = document.querySelector('.smallwidth .nav-dropdown_wrapper');

navToggles_Dropdown.forEach(elem => elem.addEventListener('click', () => {
    document.querySelector('body').classList.toggle('scrolling-disabled');

    navToggles_Dropdown_Main.classList.toggle('active');
    navDropdown.classList.toggle('active');
}));

/* Popup */
const navPopup = document.querySelector('.popup-notification');
const navToggles_Popup = document.querySelectorAll('.nav-toggle.nav-action--popup');

navToggles_Popup.forEach(elem => elem.addEventListener('click', () => {
    document.querySelector('body').classList.remove('scrolling-disabled');

    navPopup.classList.remove('active');
    window.setTimeout(() => {
        navPopup.style.display = 'none';
    }, 200)
}));
