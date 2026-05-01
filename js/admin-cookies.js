document.addEventListener("DOMContentLoaded", () => {
    const buttons = document.querySelectorAll(".theme-slider button");
    const knob = document.querySelector(".theme-knob");

    const themes = ["light", "dark", "negative"];

    function setCookie(name, value, days) {
        const date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        document.cookie = `${name}=${value}; expires=${date.toUTCString()}; path=/`;
    }

    function getCookie(name) {
        const cookies = document.cookie.split("; ");
        for (let i = 0; i < cookies.length; i++) {
            const [key, value] = cookies[i].split("=");
            if (key === name) return value;
        }
        return null;
    }

    function applyTheme(theme) {
        if (!themes.includes(theme)) theme = "light";

        document.documentElement.setAttribute("data-theme", theme);
        setCookie("admin-theme", theme, 365);

        const index = themes.indexOf(theme);
        if (knob) {
            knob.style.transform = `translateX(${index * 100}%)`;
        }

        buttons.forEach(btn => {
            btn.classList.toggle("active", btn.dataset.theme === theme);
        });
    }

    buttons.forEach(btn => {
        btn.addEventListener("click", () => {
            applyTheme(btn.dataset.theme);
        });
    });

    let savedTheme = getCookie("admin-theme");
    if (!savedTheme) savedTheme = "light";

    applyTheme(savedTheme);
});