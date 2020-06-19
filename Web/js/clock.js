const clock = document.querySelector(".clock");

function Time() {
    const date = new Date();
    const hours = date.getHours();
    const minutes = date.getMinutes();
    const day = date.getDay();
    const day_name = ["Sun", "Mon", "Tue", "Wed", "Thur", "Fri", "Sat"];
    
    clock.innerText = `${hours < 10 ? `0${hours}` : hours}:${
        minutes < 10 ? `0${minutes}` : minutes} "${day_name[day]}"`;
}

function init() {
    Time();
    setInterval(Time, 1000);
}
init();