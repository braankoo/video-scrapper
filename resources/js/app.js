import Vue from 'vue';
import VueRouter from "vue-router";
import BootstrapVue from "bootstrap-vue";

import 'bootstrap/dist/css/bootstrap.css'
import 'bootstrap-vue/dist/bootstrap-vue.css'

require('./bootstrap');

Vue.prototype.$http = axios;

Vue.use(BootstrapVue);
Vue.use(VueRouter);


import App from './App.vue';
import router from "./router";

const app = new Vue(
    {
        el: '#app',
        template: '<App/>',
        router: router,
        components: {
            App
        }
    }
);
