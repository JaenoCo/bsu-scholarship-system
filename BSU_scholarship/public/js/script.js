const carousel = document.getElementById('carousel');
let index = 0;
const total = carousel.children.length;

setInterval(() => {
  index = (index + 1) % total;
  const slideWidth = carousel.clientWidth;
  carousel.scrollTo({ left: index * slideWidth, behavior: 'smooth' });
}, 3000);