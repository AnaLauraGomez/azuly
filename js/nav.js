// Simple nav toggle for mobile
document.addEventListener('DOMContentLoaded', function () {
  var toggle = document.querySelectorAll('.nav-toggle');
  toggle.forEach(function(btn){
    btn.addEventListener('click', function(){
      var header = btn.closest('header');
      if (!header) return;
      header.classList.toggle('open');
    });
  });
});
