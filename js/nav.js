// When the user scrolls the page, execute myFunction
window.onscroll = function() {onScroll()};

// Get the navbar
const navbar = document.getElementById("nav");

// Get the offset position of the navbar
var sticky = navbar.offsetTop;

// Add the sticky class to the navbar when you reach its scroll position. Remove "sticky" when you leave the scroll position
function onScroll() {
  if (window.pageYOffset >= sticky) {
    navbar.classList.add("nav_sticky")
  } else {
    navbar.classList.remove("nav_sticky");
  }
}