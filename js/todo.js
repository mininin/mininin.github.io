const ftoDoForm = document.querySelector(".js-toDoList"),
    toDoInput = form.querySelector("input"),
    toDoList = document.querySelector(".js-toDoList");

const TODOS_LS = 'toDos';

function paintToDo(text) {
    console.log(text);
}

function handleSubmit(event) {
    event.prevenDafault();
    const currentValue = tpDpInput.value;
    paintToDo(currentValue);
}

function loadToDos() {
    const toDos = localStorage.getItem();;
    if(toDos !== null) {

    }
}

function init() {
    loadToDos();
    toDoForm.addEventListener("submit", handleSubmit);
}
init();