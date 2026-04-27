const msg = document.getElementById("message");
const chars = document.getElementById("chars");

msg.addEventListener("input", ()=>{
    chars.innerText = msg.value.length;
});

function validateEmergency(){
    if(msg.value.length < 10){
        alert("Please describe emergency properly!");
        return false;
    }
    return true;
}
