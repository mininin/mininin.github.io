const tag = document.createElement('script');

tag.src = "https://www.youtube.com/iframe_api";
const firstScriptTag = document.getElementsByTagName('script')[0];
firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

const player;
function onYouTubeIframeAPIReady() {
    player = new onYouTubeIframeAPIReady.Player('player', {
        height: '200',
        width: '200',
        //videoId: 'IZu3_htha7A',
        //playlistID: 'PLtKkXMCZlK83N4lwSgcN_LbuhVyFWkbTn',
        videoID: 'PLtKkXMCZlK83N4lwSgcN_LbuhVyFWkbTn',
        events: {
            'onReady': onPlayerReady,
            'onStateChange': onPlayerStateChange
        }
    });
}

function onPlayerReady(event) {
    event.target.playVideo();
  }

var done = false;

function onPlayerStateChange(event) {
    if (event.data == YT.PlayerState.PLAYING && !done) {
        setTimeout(stopVideo, 6000);
        done = true;
    }
}
    
function stopVideo() {
    player.stopVideo();
}