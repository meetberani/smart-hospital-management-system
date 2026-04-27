document.getElementById("search").addEventListener("keyup",function(){

let value=this.value.toLowerCase();
let rows=document.querySelectorAll("#billTable tr");

rows.forEach((row,i)=>{
if(i===0) return;

row.style.display=row.innerText.toLowerCase().includes(value)?"":"none";
});

});
