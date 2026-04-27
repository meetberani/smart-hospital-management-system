document.querySelectorAll("input,select").forEach(e=>{
e.addEventListener("focus",()=>{
e.style.boxShadow="0 0 10px #667eea";
});
e.addEventListener("blur",()=>{
e.style.boxShadow="none";
});
});
