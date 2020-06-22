const ytmusic = document.querySelector(".ytmusic"),
    music = document.querySelector(".music"),
    pli = document.querySelector(".pli");

const show_pli = "show_pli";
const in_pli = "in_pli";
const out_pli = "out_pli";

// pli button click event
pli.onclick = function() {
    ytmusic.classList.add(show_pli, in_pli);
}

ytmusic.onclick = function() {
    ytmusic.classList.add(out_pli);

    setTimeout(
        function ani_reset() {
            ytmusic.classList.remove(show_pli, in_pli, out_pli);
        }, 700
    );
}