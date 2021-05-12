import login from "./login/login";
import VueRouter from "vue-router";
import Container from "./admin/Container";
import ActorIndex from "./actor/index";
import ActorCreate from "./actor/create";

import SeriesCreate from './Series/create';
import SeriesIndex from './Series/index';
import SeriesSingle from './Series/single';

import LanguageCreate from './Language/create';
import LanguageIndex from './Language/index';

import EpisodeCreate from './Episode/create';
import EpisodeIndex from './Episode/index';
import EpisodeSingle from './Episode/single'
import Stats from "./Stats";


const router = new VueRouter(
    {
        linkActiveClass: 'open active',
        scrollBehavior: () => ({y: 0}),
        mode: 'history',
        routes: [
            {
                path: '/',
                component: login,
                name: 'Login',
                meta: {
                    requiresAuth: false
                },
            }, {
                path: '/admin',
                component: Container,
                meta: {
                    requiresAuth: true
                },
                children: [
                    {
                        path: 'actor',
                        component: ActorIndex
                    },
                    {
                        path: 'actor/create',
                        component: ActorCreate
                    },
                    {
                        path: 'series',
                        component: SeriesIndex
                    },
                    {
                        path: 'series/create',
                        component: SeriesCreate
                    },
                    {
                        path: 'series/:series',
                        component: SeriesSingle,
                        name: 'Series Single'
                    },
                    {
                        path: 'series/:series/episode/:episode',
                        component: EpisodeSingle,
                        name: 'Episode Single'
                    },
                    {
                        path: 'language',
                        component: LanguageIndex
                    },
                    {
                        path: 'language/create',
                        component: LanguageCreate
                    },
                    {
                        path: 'episode',
                        component: EpisodeIndex
                    },
                    {
                        path: 'episode/create',
                        component: EpisodeCreate
                    },
                    {
                        path: 'stats',
                        component: Stats,
                        name: 'Stats'
                    }
                ]
            }

        ]
    })
;


router.beforeEach((to, from, next) => {
    if (to.matched.some(record => record.meta.requiresAuth)) {

        if (localStorage.getItem('token') == null) {
            next({
                name: 'Login',
                params: {nextUrl: to.fullPath}
            })
        } else {
            window.axios.get('/api/check', {
                headers: {
                    Authorization: `Bearer ${localStorage.getItem('token')}`
                }
            }).then(results => {
                if (results.data) {
                    window.axios.interceptors.request.use(request => {
                        request.headers['Authorization'] = `Bearer ${localStorage.getItem('token')}`;
                        return request;
                    });
                    next();
                } else {
                    next({
                        name: 'Login',
                        params: {nextUrl: to.fullPath}
                    })
                }
            }).catch(error => {
                if (error.response.status === 401) {
                    next({
                        name: 'Login',
                        params: {nextUrl: to.fullPath}
                    })
                }
            });
        }
    } else {
        next();
    }
});
export default router;
