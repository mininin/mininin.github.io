// 1. class 및 input 받아옴
const form = document.querySelector(".js-form"),
    input = form.querySelector("input"),
    greeting = document.querySelector(".js-greetings");

// localStorage key값 
const USER_LS = "currentUser",
    SHOWING_CN = "showing";

// value 저장
function saveName(text) {
    localStorage.setItem(USER_LS, text);
}

// input에 기존 html밖으로 데이터를 보내버리는 이벤트를 삭제?
// currentValue에 input.value input에 들어가있는 값을 저장
// paintGreeting 함수와 saveName함수를 다 실행한다.
function handleSubmit(event) {
    event.preventDefault();
    const currentValue = input.value;
    //console.log(currentValue);
    paintGreeting(currentValue);
    saveName(currentValue);
}

function askForName() {
    form.classList.add(SHOWING_CN);
    form.addEventListener("submit", handleSubmit);
}

function paintGreeting(text) {
    form.classList.remove(SHOWING_CN);
    greeting.classList.add(SHOWING_CN);
    greeting.innerText = `Hello ${text}`;
}

// 1
function loadName() {
    const currentUser = localStorage.getItem(USER_LS);
    // localStorage.getItem("key") = key에 대한 value값을 currentUser에 넣음.
    if(currentUser === null) { // currentUser(key에 대한 값이)이 없으면 아무것도 실행 안함.
        askForName();
    } else {
        paintGreeting(currentUser);
        // paintGreeting 함수를 실행하는데 key에 대한 value 인자를 text에 넘겨줌.
    }
}

function init() {
    loadName();
}
init();