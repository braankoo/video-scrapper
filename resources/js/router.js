import login from "./Login/login";
import VueRouter from "vue-router";
import Container from "./Admin/Container";
import ActorIndex from "./Actor/index";
import ActorCreate from "./Actor/create";
import ActorEdit from "./Actor/edit";

import SeriesCreate from './Series/create';
import SeriesIndex from './Series/index';
import SeriesSingle from './Series/single';
import SeriesEdit from './Series/edit';

import LanguageCreate from './Language/create';
import LanguageIndex from './Language/index';
import LanguageEdit from './Language/edit';

import EpisodeIndex from './Episode/index';
import EpisodeCreate from './Episode/create';
import EpisodeStats from './Episode/stats';
import EpisodeSingle from './Episode/single'
import EpisodeEdit from './Episode/edit';


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
                        path: 'actor/:actor',
                        component: ActorEdit,
                        name: 'Edit Actor'
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
                        component: SeriesEdit,
                        name: 'Series Edit'
                    },
                    {
                        path: 'series/:series/stats',
                        component: SeriesSingle,
                        name: 'Series Stats'
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
                        path: 'language/:language',
                        component: LanguageEdit,
                        name: 'Language Edit'
                    },
                    {
                        path: 'episode',
                        component: EpisodeIndex,

                    },
                    {
                        path: 'episode/create',
                        component: EpisodeCreate,
                        name: 'Episode Create'
                    },
                    {
                        path: 'episode/:episode',
                        component: EpisodeEdit,
                        name: 'Episode Edit'
                    },
                    {
                        path: 'episode/stats',
                        component: EpisodeStats
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
