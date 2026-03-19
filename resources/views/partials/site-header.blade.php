{{-- Шапка сайта (как в ЛК) — desktop + mobile + модалка «Заказать звонок» --}}
<header class="header _desktop">
    <div class="header__top">
        <a class="logo" href="https://truck-import.ru">
            <img src="/logo.svg" alt="logo" width="500" height="300">
        </a>
        <div class="header__inner">
            <div class="header__item">
                <a class="header__phone" href="tel:+79006044614">+7 (900) 604-46-14</a>
                <button class="header__feedback" onclick="document.getElementById('header__modal').showModal()">
                    Заказать звонок
                </button>
            </div>
            <div class="header__item">
                <a class="header__phone" href="tel:+79099131186">+7 (909) 913-11-86</a>
                <p class="header__working_hours">Пн-Пт: 9:00 - 18:00</p>
            </div>
        </div>
    </div>
    <div class="header__bottom">
        <nav class="site_nav" aria-label="Сайт">
            <ul class="menu main__menu">
                <li class="menu__item" data-has-children>
                    <button class="menu__btn" onclick="toggleSubmenu(this, 'catalog-submenu')" aria-expanded="false" aria-controls="catalog-submenu">
                        Каталог запчастей
                        <svg width="14" height="14" viewBox="0 0 10 7" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" class="menu__btn-icon">
                            <path d="M9 1L5 5L1 1" stroke="white" stroke-width="2" stroke-linecap="round" />
                        </svg>
                    </button>
                    <ul class="menu menu_submenu" id="catalog-submenu" hidden>
                        <li class="menu__item"><a class="menu__link" href="https://truck-import.ru/brands/mercedes">Mercedes</a></li>
                        <li class="menu__item"><a class="menu__link" href="https://truck-import.ru/brands/man">Man</a></li>
                        <li class="menu__item"><a class="menu__link" href="https://truck-import.ru/brands/scania">Scania</a></li>
                        <li class="menu__item"><a class="menu__link" href="https://truck-import.ru/brands/volvo">Volvo</a></li>
                        <li class="menu__item"><a class="menu__link" href="https://truck-import.ru/brands/hengst">Hengst</a></li>
                        <li class="menu__item"><a class="menu__link" href="https://truck-import.ru/brands/kolbenschmidt">Kolbenschmidt</a></li>
                        <li class="menu__item"><a class="menu__link" href="https://truck-import.ru/brands/daf">Daf</a></li>
                        <li class="menu__item"><a class="menu__link" href="https://truck-import.ru/brands/iveco">Iveco</a></li>
                        <li class="menu__item"><a class="menu__link" href="https://truck-import.ru/brands/renault">Renault</a></li>
                        <li class="menu__item"><a class="menu__link" href="https://truck-import.ru/brands/valeo">Valeo</a></li>
                        <li class="menu__item"><a class="menu__link" href="https://truck-import.ru/brands/john-deere">John Deere</a></li>
                        <li class="menu__item"><a class="menu__link" href="https://truck-import.ru/brands/komatsu">Komatsu</a></li>
                        <li class="menu__item"><a class="menu__link" href="https://truck-import.ru/brands/iveco-daily">Iveco Daily</a></li>
                    </ul>
                </li>
                <li class="menu__item" data-has-children>
                    <button class="menu__btn" onclick="toggleSubmenu(this, 'buyers-submenu')" aria-expanded="false" aria-controls="buyers-submenu">
                        Покупателям
                        <svg width="14" height="14" viewBox="0 0 10 7" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" class="menu__btn-icon">
                            <path d="M9 1L5 5L1 1" stroke="white" stroke-width="2" stroke-linecap="round" />
                        </svg>
                    </button>
                    <ul class="menu menu_submenu" id="buyers-submenu" hidden>
                        <li class="menu__item"><a class="menu__link" href="https://truck-import.ru/oplata-i-dostavka">Оплата и доставка</a></li>
                        <li class="menu__item"><a class="menu__link" href="https://truck-import.ru/garantii-i-vozvrat">Гарантия и возврат</a></li>
                        <li class="menu__item"><a class="menu__link" href="https://truck-import.ru/contacts">Контакты</a></li>
                    </ul>
                </li>
                <li class="menu__item" data-has-children>
                    <button class="menu__btn" onclick="toggleSubmenu(this, 'about-submenu')" aria-expanded="false" aria-controls="about-submenu">
                        О проекте
                        <svg width="14" height="14" viewBox="0 0 10 7" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" class="menu__btn-icon">
                            <path d="M9 1L5 5L1 1" stroke="white" stroke-width="2" stroke-linecap="round" />
                        </svg>
                    </button>
                    <ul class="menu menu_submenu" id="about-submenu" hidden>
                        <li class="menu__item"><a class="menu__link" href="https://truck-import.ru/vakansii">Вакансии</a></li>
                    </ul>
                </li>
            </ul>
        </nav>
        <div class="header__buttons">
            <a class="header__account" aria-label="Аккаунт" href="{{ route('login') }}">
                <svg width="25" height="26" viewBox="0 0 25 26" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M5.85938 10.2855C5.85938 13.9379 8.84766 16.9261 12.5 16.9261C16.1523 16.9261 19.1406 13.9379 19.1406 10.2855C19.1406 6.63318 16.1523 3.6449 12.5 3.6449C8.84766 3.6449 5.85938 6.63318 5.85938 10.2855ZM17.5781 10.2855C17.5781 13.0785 15.293 15.3636 12.5 15.3636C9.70703 15.3636 7.42188 13.0785 7.42188 10.2855C7.42188 7.49255 9.70703 5.2074 12.5 5.2074C15.293 5.2074 17.5781 7.49255 17.5781 10.2855Z" fill="#C9AC37" />
                    <path d="M4.76562 22.5251C6.83594 20.4548 9.57031 19.322 12.5 19.322C15.4297 19.322 18.1641 20.4548 20.2344 22.5251L21.3477 21.4118C18.9844 19.0681 15.8398 17.7595 12.5 17.7595C9.16016 17.7595 6.01563 19.0681 3.65234 21.4118L4.76562 22.5251Z" fill="#C9AC37" />
                </svg>
            </a>
            <a class="header__button" aria-label="Корзина" href="/cart">
                <svg width="25" height="26" viewBox="0 0 25 26" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g clip-path="url(#clip0_cart_d)">
                        <path d="M24.7961 7.29121C24.6508 7.08496 24.414 6.96152 24.1617 6.96152H7.58124L6.32577 2.6334C5.83358 0.928711 4.66405 0.745117 4.18436 0.745117H0.837488C0.408582 0.745117 0.0617065 1.09277 0.0617065 1.5209C0.0617065 1.94902 0.409363 2.29668 0.837488 2.29668H4.18358C4.28983 2.29668 4.61249 2.29668 4.8328 3.05762L9.15077 18.9256C9.24452 19.26 9.54999 19.4912 9.89764 19.4912H20.4437C20.7711 19.4912 21.0633 19.2865 21.1734 18.9779L24.8906 7.99902C24.9766 7.76152 24.9406 7.49668 24.7953 7.29043L24.7961 7.29121ZM19.8984 17.9404H10.4875L8.01796 8.51387H23.0594L19.8984 17.9404ZM18.3594 21.0639C17.2805 21.0639 16.4062 21.9381 16.4062 23.017C16.4062 24.0959 17.2805 24.9701 18.3594 24.9701C19.4383 24.9701 20.3125 24.0959 20.3125 23.017C20.3125 21.9381 19.4383 21.0639 18.3594 21.0639ZM11.3281 21.0639C10.2492 21.0639 9.37499 21.9381 9.37499 23.017C9.37499 24.0959 10.2492 24.9701 11.3281 24.9701C12.407 24.9701 13.2812 24.0959 13.2812 23.017C13.2812 21.9381 12.407 21.0639 11.3281 21.0639Z" fill="black" />
                    </g>
                    <defs>
                        <clipPath id="clip0_cart_d">
                            <rect width="25" height="25" fill="white" transform="translate(0 0.357605)" />
                        </clipPath>
                    </defs>
                </svg>
                Корзина
            </a>
        </div>
    </div>
</header>

<header class="header _mobile">
    <div class="burger">
        <button class="burger__inner" onclick="toggleBurger()" aria-label="Меню">
            <span class="burger__item"></span>
            <span class="burger__item"></span>
            <span class="burger__item"></span>
        </button>
    </div>
    <a class="logo" href="https://truck-import.ru">
        <img src="/logo.svg" alt="logo" width="500" height="300">
    </a>
    <div class="header__buttons">
        <a class="header__account" aria-label="Аккаунт" href="{{ route('login') }}">
            <svg width="25" height="26" viewBox="0 0 25 26" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M5.85938 10.2855C5.85938 13.9379 8.84766 16.9261 12.5 16.9261C16.1523 16.9261 19.1406 13.9379 19.1406 10.2855C19.1406 6.63318 16.1523 3.6449 12.5 3.6449C8.84766 3.6449 5.85938 6.63318 5.85938 10.2855ZM17.5781 10.2855C17.5781 13.0785 15.293 15.3636 12.5 15.3636C9.70703 15.3636 7.42188 13.0785 7.42188 10.2855C7.42188 7.49255 9.70703 5.2074 12.5 5.2074C15.293 5.2074 17.5781 7.49255 17.5781 10.2855Z" fill="#C9AC37" />
                <path d="M4.76562 22.5251C6.83594 20.4548 9.57031 19.322 12.5 19.322C15.4297 19.322 18.1641 20.4548 20.2344 22.5251L21.3477 21.4118C18.9844 19.0681 15.8398 17.7595 12.5 17.7595C9.16016 17.7595 6.01563 19.0681 3.65234 21.4118L4.76562 22.5251Z" fill="#C9AC37" />
            </svg>
        </a>
        <a class="header__button" aria-label="Корзина" href="/cart">
            <svg width="25" height="26" viewBox="0 0 25 26" fill="none" xmlns="http://www.w3.org/2000/svg">
                <g clip-path="url(#clip0_cart_m)">
                    <path d="M24.7961 7.29121C24.6508 7.08496 24.414 6.96152 24.1617 6.96152H7.58124L6.32577 2.6334C5.83358 0.928711 4.66405 0.745117 4.18436 0.745117H0.837488C0.408582 0.745117 0.0617065 1.09277 0.0617065 1.5209C0.0617065 1.94902 0.409363 2.29668 0.837488 2.29668H4.18358C4.28983 2.29668 4.61249 2.29668 4.8328 3.05762L9.15077 18.9256C9.24452 19.26 9.54999 19.4912 9.89764 19.4912H20.4437C20.7711 19.4912 21.0633 19.2865 21.1734 18.9779L24.8906 7.99902C24.9766 7.76152 24.9406 7.49668 24.7953 7.29043L24.7961 7.29121ZM19.8984 17.9404H10.4875L8.01796 8.51387H23.0594L19.8984 17.9404ZM18.3594 21.0639C17.2805 21.0639 16.4062 21.9381 16.4062 23.017C16.4062 24.0959 17.2805 24.9701 18.3594 24.9701C19.4383 24.9701 20.3125 24.0959 20.3125 23.017C20.3125 21.9381 19.4383 21.0639 18.3594 21.0639ZM11.3281 21.0639C10.2492 21.0639 9.37499 21.9381 9.37499 23.017C9.37499 24.0959 10.2492 24.9701 11.3281 24.9701C12.407 24.9701 13.2812 24.0959 13.2812 23.017C13.2812 21.9381 12.407 21.0639 11.3281 21.0639Z" fill="black" />
                </g>
                <defs>
                    <clipPath id="clip0_cart_m">
                        <rect width="25" height="25" fill="white" transform="translate(0 0.357605)" />
                    </clipPath>
                </defs>
            </svg>
        </a>
    </div>
    <div class="burger__content" id="burgerContent">
        <nav class="site_nav_mobile" aria-label="Сайт">
            <ul class="menu main__menu">
                <li class="menu__item" data-has-children>
                    <button class="menu__btn" onclick="toggleMobileSubmenu(this, 'mobile-catalog-submenu')" aria-expanded="false">
                        Каталог запчастей
                        <svg width="14" height="14" viewBox="0 0 10 7" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" class="menu__btn-icon">
                            <path d="M9 1L5 5L1 1" stroke="white" stroke-width="2" stroke-linecap="round" />
                        </svg>
                    </button>
                    <ul class="menu menu_submenu" id="mobile-catalog-submenu" hidden>
                        <li class="menu__item"><a class="menu__link" href="https://truck-import.ru/brands/mercedes">Mercedes</a></li>
                        <li class="menu__item"><a class="menu__link" href="https://truck-import.ru/brands/man">Man</a></li>
                        <li class="menu__item"><a class="menu__link" href="https://truck-import.ru/brands/scania">Scania</a></li>
                    </ul>
                </li>
                <li class="menu__item" data-has-children>
                    <button class="menu__btn" onclick="toggleMobileSubmenu(this, 'mobile-buyers-submenu')" aria-expanded="false">
                        Покупателям
                        <svg width="14" height="14" viewBox="0 0 10 7" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" class="menu__btn-icon">
                            <path d="M9 1L5 5L1 1" stroke="white" stroke-width="2" stroke-linecap="round" />
                        </svg>
                    </button>
                    <ul class="menu menu_submenu" id="mobile-buyers-submenu" hidden>
                        <li class="menu__item"><a class="menu__link" href="https://truck-import.ru/oplata-i-dostavka">Оплата и доставка</a></li>
                        <li class="menu__item"><a class="menu__link" href="https://truck-import.ru/garantii-i-vozvrat">Гарантия и возврат</a></li>
                        <li class="menu__item"><a class="menu__link" href="https://truck-import.ru/contacts">Контакты</a></li>
                    </ul>
                </li>
                <li class="menu__item" data-has-children>
                    <button class="menu__btn" onclick="toggleMobileSubmenu(this, 'mobile-about-submenu')" aria-expanded="false">
                        О проекте
                        <svg width="14" height="14" viewBox="0 0 10 7" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" class="menu__btn-icon">
                            <path d="M9 1L5 5L1 1" stroke="white" stroke-width="2" stroke-linecap="round" />
                        </svg>
                    </button>
                    <ul class="menu menu_submenu" id="mobile-about-submenu" hidden>
                        <li class="menu__item"><a class="menu__link" href="https://truck-import.ru/vakansii">Вакансии</a></li>
                    </ul>
                </li>
            </ul>
        </nav>
    </div>
</header>

<dialog class="header__modal" id="header__modal">
    <div class="feedback">
        <div style="padding: 3rem; color: white;">
            <h3 style="font-size: 2.4rem; margin-bottom: 2rem; color: #C9AC37;">Заказать звонок</h3>
            <form style="display: flex; flex-direction: column; gap: 1.5rem;">
                <input type="text" placeholder="Имя" style="width: 100%; padding: 1.2rem; background: #2A2A2A; border: 1px solid #BCA13480; border-radius: .5rem; color: white; font-size: 1.6rem;">
                <input type="tel" placeholder="Телефон" style="width: 100%; padding: 1.2rem; background: #2A2A2A; border: 1px solid #BCA13480; border-radius: .5rem; color: white; font-size: 1.6rem;">
                <button style="background: #C9AC37; color: #141313; padding: 1.2rem; border: none; border-radius: .5rem; font-size: 1.8rem; font-weight: 600; cursor: pointer; margin-top: 1rem;">Отправить</button>
            </form>
        </div>
    </div>
    <div class="header__modal_close" onclick="document.getElementById('header__modal').close()">
        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M1 1L10 10M10 10L19 19M10 10L1 19M10 10L19 1" stroke="#F7F7F7" stroke-width="2" stroke-linecap="round" />
        </svg>
    </div>
</dialog>

<script>
function toggleSubmenu(button, submenuId) {
    const submenu = document.getElementById(submenuId);
    const isOpen = !submenu.hidden;
    document.querySelectorAll('.menu_submenu').forEach(menu => { menu.hidden = true; });
    document.querySelectorAll('.menu__btn[aria-expanded="true"]').forEach(btn => { btn.setAttribute('aria-expanded', 'false'); });
    if (!isOpen) { submenu.hidden = false; button.setAttribute('aria-expanded', 'true'); }
}
function toggleMobileSubmenu(button, submenuId) {
    const submenu = document.getElementById(submenuId);
    const isOpen = !submenu.hidden;
    if (isOpen) { submenu.hidden = true; button.setAttribute('aria-expanded', 'false'); }
    else { submenu.hidden = false; button.setAttribute('aria-expanded', 'true'); }
}
function toggleBurger() {
    const burgerBtn = document.querySelector('.burger__inner');
    const burgerContent = document.getElementById('burgerContent');
    const isActive = burgerBtn.classList.contains('_active');
    if (isActive) {
        burgerBtn.classList.remove('_active');
        burgerContent.classList.remove('_active');
        document.body.classList.remove('stop-scrolling');
    } else {
        burgerBtn.classList.add('_active');
        burgerContent.classList.add('_active');
        document.body.classList.add('stop-scrolling');
    }
}
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('header__modal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            const rect = modal.getBoundingClientRect();
            const isClickOutside = e.clientX < rect.left || e.clientX > rect.right || e.clientY < rect.top || e.clientY > rect.bottom;
            if (isClickOutside) modal.close();
        });
    }
    document.querySelectorAll('.burger__content a').forEach(link => {
        link.addEventListener('click', function() {
            const burgerBtn = document.querySelector('.burger__inner');
            const burgerContent = document.getElementById('burgerContent');
            if (burgerBtn && burgerBtn.classList.contains('_active')) {
                burgerBtn.classList.remove('_active');
                burgerContent.classList.remove('_active');
                document.body.classList.remove('stop-scrolling');
            }
        });
    });
});
</script>
