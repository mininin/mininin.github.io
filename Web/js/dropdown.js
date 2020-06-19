// className 받아옴
const child_menu = document.querySelector(".child_menu"),
    dropdown_menu = document.querySelector(".dropdown_menu");

// className Add
const showing = "showing";

child_menu.onmouseover = function() {
    dropdown_menu.classList.add(showing);
}

child_menu.onmouseout = function() {
    dropdown_menu.classList.remove(showing);
}

dropdown_menu.onmouseout = function() {
    dropdown_menu.classList.remove(showing);
}

/*
function test() {
    if(dropdown_menu !== showing) {
        child_menu.addEventListener("mouseover", function mouse_on() {
            dropdown_menu.classList.add(showing);
            console.log("mouse on!!");
        });
    } else {
        child_menu.addEventListener("mouseout", function mouse_off() {
            dropdown_menu.classList.remove(showing);
            console.log("mouse out!!");
        });
    }
}

test();
*/