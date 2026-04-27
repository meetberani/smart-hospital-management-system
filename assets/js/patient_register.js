document.querySelectorAll("input,select").forEach(i=>{
i.addEventListener("focus",()=>{
i.style.boxShadow="0 0 10px #00c6ff";
});
i.addEventListener("blur",()=>{
i.style.boxShadow="none";
});
});
