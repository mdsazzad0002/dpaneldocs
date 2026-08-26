import { ref } from 'vue';

const isDark = ref(document.documentElement.classList.contains('dark'));

export function useTheme() {
    function apply(dark) {
        isDark.value = dark;
        document.documentElement.classList.toggle('dark', dark);
        try {
            localStorage.setItem('theme', dark ? 'dark' : 'light');
        } catch (e) {
            // storage unavailable, ignore
        }
    }

    function toggle() {
        apply(!isDark.value);
    }

    return { isDark, toggle };
}
