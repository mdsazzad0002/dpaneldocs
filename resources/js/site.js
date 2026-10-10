// Small progressive enhancements for the public (Blade) pages. Everything
// here is optional: the pages work, and every form submits, without it.

const $ = (selector, root = document) => root.querySelector(selector);
const $$ = (selector, root = document) => Array.from(root.querySelectorAll(selector));

// Dark mode toggle
$$('[data-theme-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
        const dark = !document.documentElement.classList.contains('dark');
        document.documentElement.classList.toggle('dark', dark);
        try { localStorage.setItem('theme', dark ? 'dark' : 'light'); } catch (e) { /* storage unavailable */ }
    });
});

// Mobile menu
$$('[data-menu-toggle]').forEach((button) => {
    const menu = document.getElementById(button.getAttribute('aria-controls'));
    button.addEventListener('click', () => {
        const open = menu.classList.toggle('hidden') === false;
        button.setAttribute('aria-expanded', String(open));
    });
});

// Press "/" to jump to search
document.addEventListener('keydown', (event) => {
    const tag = (event.target.tagName || '').toLowerCase();
    if (event.key !== '/' || ['input', 'textarea', 'select'].includes(tag) || event.target.isContentEditable) return;
    const input = $$('[data-search-input]').find((el) => el.offsetParent !== null);
    if (input) {
        event.preventDefault();
        input.focus();
    }
});

// Copy buttons for code blocks and [data-copy] elements
const copy = async (text, button) => {
    try {
        await navigator.clipboard.writeText(text);
        const label = button.textContent;
        button.textContent = 'Copied';
        setTimeout(() => { button.textContent = label; }, 1500);
    } catch (e) { /* clipboard unavailable */ }
};

$$('.doc-prose pre').forEach((pre) => {
    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'copy-button';
    button.textContent = 'Copy';
    button.addEventListener('click', () => copy(pre.querySelector('code')?.innerText ?? pre.innerText, button));
    pre.appendChild(button);
});

$$('[data-copy]').forEach((button) => {
    button.addEventListener('click', () => copy($(button.dataset.copy).innerText, button));
});

$$('[data-copy-text]').forEach((button) => {
    button.addEventListener('click', () => copy(button.dataset.copyText, button));
});

// Highlight the table-of-contents entry for the section being read
const tocLinks = $$('[data-toc] a');
if (tocLinks.length && 'IntersectionObserver' in window) {
    const byId = new Map(tocLinks.map((link) => [decodeURIComponent(link.hash.slice(1)), link]));
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) return;
            tocLinks.forEach((link) => link.classList.remove('is-active'));
            byId.get(entry.target.id)?.classList.add('is-active');
        });
    }, { rootMargin: '-80px 0px -70% 0px' });
    byId.forEach((_, id) => { const el = document.getElementById(id); if (el) observer.observe(el); });
}

// "Was this page helpful?" — without JS the Yes/No buttons submit directly;
// with JS they reveal an optional comment box, then post in the background.
$$('[data-feedback]').forEach((form) => {
    const value = $('[data-feedback-value]', form);
    const followUp = $('[data-feedback-comment]', form);

    $$('button[name="helpful"]', form).forEach((button) => {
        button.addEventListener('click', (event) => {
            event.preventDefault();
            value.value = button.value;
            $$('button[name="helpful"]', form).forEach((b) => b.setAttribute('aria-pressed', String(b === button)));
            followUp.classList.remove('hidden');
            $('textarea', followUp)?.focus();
        });
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: new FormData(form),
            });
            if (!response.ok) throw new Error('Request failed');
            form.innerHTML = '<p class="text-sm font-medium text-emerald-700 dark:text-emerald-400">Thanks for the feedback! It helps us improve this page.</p>';
        } catch (e) {
            form.submit();
        }
    });
});

// Star rating input: fill stars up to the hovered/selected one
$$('[data-rating]').forEach((group) => {
    const inputs = $$('input[type="radio"]', group);
    const paint = (upTo) => inputs.forEach((input) => {
        input.nextElementSibling.classList.toggle('is-on', Number(input.value) <= upTo);
    });
    const selected = () => Number(inputs.find((i) => i.checked)?.value ?? 0);

    inputs.forEach((input) => {
        input.addEventListener('change', () => paint(selected()));
        input.nextElementSibling.addEventListener('mouseenter', () => paint(Number(input.value)));
    });
    group.addEventListener('mouseleave', () => paint(selected()));
    paint(selected());
});
