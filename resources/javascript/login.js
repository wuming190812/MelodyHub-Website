function validateForm() {
    let isValid = true;
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;

    // Hide all error messages
    document.getElementById('email-error').style.display = 'none';
    document.getElementById('password-error').style.display = 'none';

    // Validate email
    if (!email) {
        document.getElementById('email-error').style.display = 'block';
        isValid = false;
    }

    // Validate password
    if (!password) {
        document.getElementById('password-error').style.display = 'block';
        isValid = false;
    }

    return isValid;
}