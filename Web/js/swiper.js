/*
swiper 참고 링크
https://www.biew.co.kr/entry/%EB%B0%98%EC%9D%91%ED%98%95-%EC%8A%AC%EB%9D%BC%EC%9D%B4%EB%8D%94-Swiper
*/

// 기본 슬라이드
// new Swiper('.swiper-container');

// 옵션 적용 슬라이드 
var mySwper = new Swiper('.swiper-container',{
    // 옵션 입력 라인
    autoplay: {
        delay: 5000
    },
    pagination: {
        el: '.swiper-pagination',
        type: 'bullets',
        clickable: 'true'
    }
});