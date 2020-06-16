// id = "title"인 요소에 Click이벤트에 대한 색 변경하기.
/*
const title = document.querySelector("#title");

const BASE_COLOR = "rgb(255, 255, 255)";
const OTHER_COLOR = "#7f8c8d";

function handleClick() {
        const currentColor = title.style.color;

        if (currentColor === BASE_COLOR) {
            title.style.color = OTHER_COLOR;
        } else {
            title.style.color = BASE_COLOR;
        }
}

function init() {
    title.style.color = BASE_COLOR;
    title.addEventListener("click", handleClick);
}

init();
*/

// id = "title"이란 요소에 class = "btn"이란 className을 css에서 주고 나서
// .btn에 pointer라는 옵션을 주고 클릭 할 떄 마다 기존 색 바꾸는 옵션을 더하는데
// className을 직접적으로 바꾸는 옵션을 하면 전에 있던 요소를 존중하지 않아서 btn속성이 CLICKED_CLASS
// 로 바뀌면서 사라짐 그래서 classLiist.add/remove로 className에 추가하고 뺴고하여 존중됨. 
/*
const title = document.querySelector("#title");

const CLICKED_CLASS = "clicked";

function handleClick() {
    const hasClass = title.classList.contains(CLICKED_CLASS);
    //const currentClass = title.className;
    if(!hasClass) {
    //if(currentClass !== CLICKED_CLASS) {
        title.classList.add(CLICKED_CLASS);
        //title.className = CLICKED_CLASS;
    } else {
        title.classList.remove(CLICKED_CLASS);
        //title.className = "";
    }
}

function init() {
    title.addEventListener("click", handleClick);
}
init();
*/