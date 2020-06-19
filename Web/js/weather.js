const API_KEY = "268a837324d7bb6c073afdd674f06900";

const weather = document.querySelector(".weather");

function getWeather(lat, lon) {
    fetch(
        `https://api.openweathermap.org/data/2.5/weather?lat=${lat}&lon=${lon}&appid=${API_KEY}&units=metric`
    )
        .then(function(response) {
            return response.json();
        })
        .then(function(json) {
            const temperature = parseInt(json.main.temp);
            const place = json.name;
            const main = json.weather[0].main;
            weather.innerText = `${temperature}°C ${main} / ${place}`;
        });
}

function latlon(location) {
    const lat = location.coords.latitude;
    const lon = location.coords.longitude;
    getWeather(lat, lon);
}

function askForGeolocation() {
    navigator.geolocation.getCurrentPosition(latlon);
}

function init() {
    askForGeolocation();
}
init();