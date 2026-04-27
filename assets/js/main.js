function confirmDelete(){
    return confirm("Are you sure?");
}

setTimeout(()=>{
    let alerts=document.querySelectorAll('.alert');
    alerts.forEach(a=>a.style.display="none");
},4000);
