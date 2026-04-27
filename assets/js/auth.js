document.querySelectorAll(".big-btn").forEach(btn=>{
btn.addEventListener("mouseenter",()=>{
btn.style.boxShadow="0 0 15px #00c6ff";
});
btn.addEventListener("mouseleave",()=>{
btn.style.boxShadow="none";
});
});
