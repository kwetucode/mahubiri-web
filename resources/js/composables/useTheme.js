import { ref, watch, onMounted } from 'vue';

const getDefaultTheme = () =>
    localStorage.getItem('mahubiri-theme')
    || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');

const theme = ref(getDefaultTheme());

const applyTheme = (value) => {
    const root = document.documentElement;
    if (value === 'dark') {
        root.classList.add('dark');
    } else {
        root.classList.remove('dark');
    }
    localStorage.setItem('mahubiri-theme', value);
};

export function useTheme() {
    onMounted(() => {
        applyTheme(theme.value);
    });

    watch(theme, (val) => {
        applyTheme(val);
    });

    const setTheme = (val) => {
        theme.value = val;
    };

    const toggleTheme = () => {
        theme.value = theme.value === 'dark' ? 'light' : 'dark';
    };

    const isDark = () => theme.value === 'dark';

    return { theme, setTheme, toggleTheme, isDark };
}
