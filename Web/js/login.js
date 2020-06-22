const bt_login = document.querySelector(".bt_login"),
    login_screen = document.querySelector(".login_screen");

bt_login.onclick = function() {
    login_screen.classList.add(show_pli, in_pli);
}

login_screen.onclick = function() {
    login_screen.classList.add(out_pli);

    setTimeout(
        function ani_reset() {
            login_screen.classList.remove(show_pli, in_pli, out_pli);
        }, 700
    );
}