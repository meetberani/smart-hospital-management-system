// Confirm logout
function confirmLogout() {
    return confirm("Are you sure you want to logout?");
}

// Simple alert function
function showAlert(msg) {
    alert(msg);
}

// Date validation (appointment)
function validateDate(inputDate) {
    const today = new Date().toISOString().split("T")[0];
    if (inputDate < today) {
        alert("Please select a valid future date");
        return false;
    }
    return true;
}
