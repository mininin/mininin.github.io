function Time() {
    var time = document.getElementById("time"); // 출력할 장소 선택
    var currentTime = new Date();               // 현재시간
    var AmPm = "am";
    var currentHours = addZeros(currentTime.getHours(), 2);
    var currentMinutes = addZeros(currentTime.getMinutes(), 2);

    if(currentHours >= 12)
    {
        AmPm = "pm";
        currentHours = addZeros(currentHours - 12, 2);
    }
    time.innerHTML = currentHours + ":" + currentMinutes + " " + "<font size=2pt>" + AmPm + "</font>";

    setTimeout("Time()", 1000);
}
// 자릿수 맞추기
function addZeros(num, digit)
{
    var zero = '';
    num = num.toString();
    if (num.length < digit){
    
        for (i = 0; i < digit - num.length; i++){
            zero += '0';
        }
    }
    return zero + num;
}