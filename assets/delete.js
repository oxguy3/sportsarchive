// this makes the Cancel button on the DeleteType form work
window.addEventListener('DOMContentLoaded', function(event) {
  let button = document.getElementById("delete_cancel");
  button.onclick = function () {
    window.history.back();
  }
});
