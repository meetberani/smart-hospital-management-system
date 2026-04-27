const slider = document.querySelector('#slider');

new bootstrap.Carousel(slider,{
interval:2500,
ride:true
});
new bootstrap.Carousel(document.querySelector('#slider'),{
interval:2000
});
