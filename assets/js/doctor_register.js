document.querySelectorAll("input").forEach(i=>{
i.addEventListener("focus",()=>{
i.style.boxShadow="0 0 10px #38ef7d";
});
i.addEventListener("blur",()=>{
i.style.boxShadow="none";
});
});
