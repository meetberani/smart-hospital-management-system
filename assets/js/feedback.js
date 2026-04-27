const feedback = document.getElementById("feedback");
const chars = document.getElementById("chars");

feedback.addEventListener("input", ()=>{
    chars.innerText = feedback.value.length;
});

function validateFeedback(){
    if(feedback.value.length < 10){
        alert("Please write at least 10 characters.");
        return false;
    }
    return true;
}
