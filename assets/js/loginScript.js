const container = document.getElementById('container');
const registerBtn = document.getElementById('signUp');
const loginBtn = document.getElementById('signIn');
const signInButton = document.getElementById('signIn');
const signUpButton = document.getElementById('signUp');

registerBtn.addEventListener('click', () => {
    container.classList.add("active");
});

loginBtn.addEventListener('click', () => {
    container.classList.remove("active");
});

