// Minimal JS to try audio autoplay on user interaction
document.addEventListener('DOMContentLoaded', function(){
  var audio = document.getElementById('mplp-audio');
  function tryPlay(){ if(!audio) return; audio.volume = 0.33; audio.play().catch(()=>{}); window.removeEventListener('click', tryPlay); window.removeEventListener('scroll', tryPlay); }
  window.addEventListener('click', tryPlay, {once:true}); window.addEventListener('scroll', tryPlay, {once:true});
});
