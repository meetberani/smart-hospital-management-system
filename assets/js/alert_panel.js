document.getElementById("search").addEventListener("keyup",function(){

let v=this.value.toLowerCase();
let rows=document.querySelectorAll("#alertTable tr");

rows.forEach((r,i)=>{
if(i===0)return;

r.style.display=r.innerText.toLowerCase().includes(v)?"":"none";
});

});
