import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import VueApexCharts from "vue3-apexcharts";
import VueTelInput from 'vue-tel-input';
import { i18nVue } from 'laravel-vue-i18n';

createInertiaApp({
  resolve: name => {
    const pages = import.meta.glob('./Pages/**/*.vue', { eager: true })
    return pages[`./Pages/${name}.vue`]
  },
  setup({ el, App, props, plugin }) {
    createApp({ render: () => h(App, props) })
      .use(plugin)
      .use(VueApexCharts)
      .use(VueTelInput)
      .use(i18nVue, { 
        resolve: async lang => {
            const langs = import.meta.glob('../../lang/*.json');
            return await langs[`../../lang/${lang}.json`]();
        }
      })
      .mount(el)
  },
  progress: {
    delay: 250,
    color: '#198754',
    includeCSS: true,
    showSpinner: false,
  },
})