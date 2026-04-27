document.addEventListener("DOMContentLoaded",()=>{

document.querySelectorAll(".card").forEach(card=>{
card.addEventListener("mouseenter",()=>{
card.style.boxShadow="0 10px 20px rgba(0,0,0,.2)";
});
card.addEventListener("mouseleave",()=>{
card.style.boxShadow="0 4px 10px rgba(0,0,0,.1)";
});
});

});
