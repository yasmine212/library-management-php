
document.addEventListener('DOMContentLoaded', () => {
    // Client-side email validation for users
    const userForm = document.querySelector('form');
    if (userForm && window.location.pathname.includes('users.php')) {
        userForm.addEventListener('submit', (e) => {
            const email = document.getElementById('email').value;
            if (!validateEmail(email)) {
                e.preventDefault();
                alert('Veuillez entrer une adresse email valide.');
            }
        });
    }

    // Auto-close success messages after animation
    const messages = document.querySelectorAll('.message');
    messages.forEach(message => {
        setTimeout(() => {
            message.style.display = 'none';
        }, 5000); // Match CSS fadeOut duration
    });
});

function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function openList() {
    document.getElementById('modal').style.display = 'flex';
    document.getElementById('book-list').style.display = 'block';
    document.body.classList.add('modal-active');
}

function closeList() {
    document.getElementById('modal').style.display = 'none';
    document.getElementById('book-list').style.display = 'none';
    document.body.classList.remove('modal-active');
}

window.onclick = function(event) {
    const modal = document.getElementById('modal');
    if (event.target === modal) {
        closeList();
    }
};