<template>
    <div id="admin">
        <b-container :fluid="true">
            <b-row>
                <b-col cols="3">
                    <sidebar-menu :menu="menu">
                        <span slot="toggle-icon"><i class="fa fa fa-arrows-h"></i></span>
                        <span slot="footer" class="text-center"><a href=""
                                                                   class="vsm--link vsm--link_level-1"
                                                                   @click.self.prevent="logout"
                        >Log out</a></span>
                    </sidebar-menu>
                </b-col>
                <b-col cols="9" class="pt-3">
                    <router-view></router-view>
                </b-col>
            </b-row>
        </b-container>
    </div>
</template>

<script>
import {SidebarMenu} from 'vue-sidebar-menu'

export default {
    components: {
        SidebarMenu
    },
    name: "Container",
    data() {
        return {
            menu: [
                {
                    header: true,
                    title: 'Navigation',
                    hiddenOnCollapse: true
                },
                {
                    title: 'Home',
                    href: '/home',
                    icon: 'fa fa-columns'
                },
                {
                    title: 'Actors',
                    icon: 'fa fa-user-circle',
                    child: [
                        {
                            href: '/admin/actor',
                            title: 'Show All'
                        },
                        {
                            href: '/admin/actor/create',
                            title: 'Add New'
                        }
                    ]
                },
                {
                    title: 'Series',
                    icon: 'fa fa-tv',
                    child: [
                        {
                            href: '/admin/series',
                            title: 'Show All'
                        },
                        {
                            href: '/admin/series/create',
                            title: 'Add Series'
                        }
                    ]
                },
                {
                    title: 'Languages',
                    icon: 'fa fa-pencil',
                    child: [
                        {
                            href: '/admin/language',
                            title: 'Show All'
                        },
                        {
                            href: '/admin/language/create',
                            title: 'Add Language'
                        }
                    ]
                },
                {
                    title: 'Episode',
                    icon: 'fa fa-play-circle',
                    child: [
                        {
                            href: '/admin/episode',
                            title: 'Show All'
                        },
                        {
                            href: '/admin/episode/create',
                            title: 'Add Episode'
                        },
                        {
                            href: '/admin/episode/stats',
                            title: 'Stats'
                        },
                    ]
                },
                {
                    title: 'Stats',
                    icon: 'fa  fa-table',
                    href: '/admin/stats'
                }

            ]
        }
    },
    methods: {
        logout() {
            this.$http.post('/logout').then(() => {
                localStorage.removeItem('token');
                this.$router.push({'name': 'Login'});
            })
        }
    }
}

</script>

<style>
#admin {
    background-color: #283944;
    font-family: 'Roboto', sans-serif;
    font-size: 16px;
    line-height: 24px;
    color: #b7c4c9;
    min-height: 100%;
}

.v-sidebar-menu .vsm--toggle-btn:after,
.v-sidebar-menu .vsm--arrow:after {
    font: normal normal normal 14px/1 FontAwesome;
}

.v-sidebar-menu .vsm--toggle-btn:after {
    content: "\f337";
}
</style>
