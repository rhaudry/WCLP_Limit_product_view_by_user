// setup var
var wclp_checkbox = document.getElementById("private_product");
var wclp_box = document.getElementById("div_wclp");
var wclp_main_box = document.getElementById("div_wclp_user_list");

// event listener
window.addEventListener('load', wclp_checkbox_state);
wclp_checkbox.addEventListener("click", wclp_checkbox_state);

function wclp_checkbox_state() {


  // If the checkbox is checked, display the div box
  if (wclp_checkbox.checked == true){
    wclp_box.style.transition = '0.4s';
    wclp_box.style.opacity = "1";
  } else {
    wclp_box.style.transition = '0.4s';
    wclp_box.style.opacity = "0";
  }
}
