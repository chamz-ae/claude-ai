// Smooth scrolling
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();

        document.querySelector(this.getAttribute('href')).scrollIntoView({
            behavior: 'smooth'
        });
    });
});

// Active navigation link
const sections = document.querySelectorAll('section');
const navLinks = document.querySelectorAll('nav ul li a');

window.addEventListener('scroll', () => {
    let current = '';

    sections.forEach(section => {
        const sectionTop = section.offsetTop;
        const sectionHeight = section.clientHeight;

        if (pageYOffset >= sectionTop - sectionHeight / 3) {
            current = section.getAttribute('id');
        }
    });

    navLinks.forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('href').slice(1) === current) {
            link.classList.add('active');
        }
    });
    
});
document.addEventListener('DOMContentLoaded', function() {
    // ... (kode sebelumnya tetap ada)

    // Navbar scroll effect
    window.addEventListener('scroll', function() {
        const header = document.querySelector('header');
        header.classList.toggle('scrolled', window.scrollY > 0);
    });

    // Smooth reveal for sections
    const revealSection = function(entries, observer) {
        const [entry] = entries;
        if (!entry.isIntersecting) return;
        entry.target.classList.remove('section-hidden');
        observer.unobserve(entry.target);
    };

    const sectionObserver = new IntersectionObserver(revealSection, {
        root: null,
        threshold: 0.15,
    });

    document.querySelectorAll('section').forEach(function(section) {
        sectionObserver.observe(section);
        section.classList.add('section-hidden');
    });

    // Typing effect for the title
    const title = document.querySelector('#home h2');
    const text = title.textContent;
    title.innerHTML = '';
    let i = 0;
    const typeWriter = () => {
        if (i < text.length) {
            title.innerHTML += text.charAt(i);
            i++;
            setTimeout(typeWriter, 100);
        }
    }
    setTimeout(typeWriter, 1000);

    // ... (kode sebelumnya tetap ada)
});

document.addEventListener('DOMContentLoaded', function() {
    // Theme toggle functionality
    const themeToggle = document.getElementById('theme-toggle');
    const prefersDarkScheme = window.matchMedia("(prefers-color-scheme: dark)");

    function toggleTheme() {
        const currentTheme = document.body.getAttribute('data-theme');
        if (currentTheme === 'dark') {
            document.body.setAttribute('data-theme', 'light');
            localStorage.setItem('theme', 'light');
        } else {
            document.body.setAttribute('data-theme', 'dark');
            localStorage.setItem('theme', 'dark');
        }
    }

    themeToggle.addEventListener('click', toggleTheme);

    // Check for saved theme preference or use device preference
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme) {
        document.body.setAttribute('data-theme', savedTheme);
    } else if (prefersDarkScheme.matches) {
        document.body.setAttribute('data-theme', 'dark');
    } else {
        document.body.setAttribute('data-theme', 'light');
    }
});

window.addEventListener('scroll', function() {
    const scrollPosition = window.pageYOffset;
    const layer1 = document.querySelector('.layer-1');
    const layer2 = document.querySelector('.layer-2');
    
    layer1.style.transform = `translateY(${scrollPosition * 0.1}px)`;
    layer2.style.transform = `translateY(${scrollPosition * 0.2}px)`;
});

function createShootingStar() {
    const star = document.createElement('div');
    star.classList.add('shooting-star');
    star.style.left = `${Math.random() * window.innerWidth}px`;
    star.style.top = `${Math.random() * window.innerHeight / 2}px`;
    star.style.animationDuration = `${Math.random() * 2 + 3}s`;
    document.querySelector('#home').appendChild(star);
    
    setTimeout(() => {
        star.remove();
    }, 5000);
}

setInterval(createShootingStar, 1000);

// Add this to index.js

document.addEventListener('DOMContentLoaded', () => {
    const hamburger = document.getElementById('hamburger');
    const navMenu = document.getElementById('nav-menu');

    hamburger.addEventListener('click', () => {
        navMenu.classList.toggle('active');
    });
});

document.addEventListener('DOMContentLoaded', function() {
    // ... (previous code remains the same) ...

    // Form validation and submission
    const contactForm = document.getElementById('contactForm');
    const formStatus = document.getElementById('formStatus');

    contactForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const nameInput = contactForm.querySelector('input[name="name"]');
        const emailInput = contactForm.querySelector('input[name="email"]');
        const messageInput = contactForm.querySelector('textarea[name="message"]');

        if (!nameInput.value || !emailInput.value || !messageInput.value) {
            formStatus.textContent = 'Please fill in all fields.';
            formStatus.style.color = 'red';
            return;
        }

        if (!isValidEmail(emailInput.value)) {
            formStatus.textContent = 'Please enter a valid email address.';
            formStatus.style.color = 'red';
            return;
        }

        const formData = new FormData(contactForm);

        fetch(contactForm.action, {
            method: 'POST',
            body: formData,
        })
        .then(response => response.text())
        .then(result => {
            formStatus.textContent = result;
            formStatus.style.color = 'green';
            contactForm.reset();
        })
        .catch(error => {
            formStatus.textContent = 'An error occurred. Please try again later.';
            formStatus.style.color = 'red';
            console.error('Error:', error);
        });
    });

    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
});