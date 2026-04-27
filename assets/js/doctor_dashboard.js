const notes=document.getElementById("notes");
const chars=document.getElementById("chars");

notes.addEventListener("input",()=>{
chars.innerText=notes.value.length;
});

function validateNotes(){
if(notes.value.length<10){
alert("Please write proper medical notes");
return false;
}
return true;
}
