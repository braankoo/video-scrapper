<template>
    <div id="Episodes">
        <b-card header="Episodes">
            <b-row class="mb-5">
                <b-col>
                    <actors @selected-actors="filters.actors = $event"/>
                </b-col>
                <b-col>
                    <languages @selected-languages="filters.languages = $event"/>
                </b-col>
                <b-col>
                    <date @selected-date="filters.date = $event"/>
                </b-col>
            </b-row>
            <b-table :api-url="`/api/series/${$route.params.series}`"
                     id="Episodes"
                     :busy.sync="isBusy"
                     :items="getEpisodes"
                     :fields="fields"
                     :current-page="currentPage"
                     :per-page="perPage"
                     table-variant="light"
                     head-variant="light"
                     :striped="true"
                     :bordered="true"
                     :outlined="true"
                     ref="episode-table"
                     :filter="filters"
            >
                <template #cell(name)="data" class="text-center">
                    <router-link
                        :to="{name: 'Episode Single', params: {series: $route.params.series, episode: data.item.id}}">
                        {{ data.item.name }}
                    </router-link>
                </template>
                <template #cell(actors)="data" class="text-center">
                    <b-badge variant="primary" v-for="actor in data.item.actors.split(',')" v-bind:key=actor.id>
                        {{ actor }}
                    </b-badge>
                    &nbsp;&nbsp;
                </template>
                <template #cell(id)="data" class="text-center">
                    <b-button variant="danger" class="btn-sm" @click="removeEpisode(data.item.id)"><i
                        class="fa fa-window-close"></i></b-button>
                </template>
            </b-table>
            <b-pagination
                v-model="currentPage"
                :total-rows="totalRows"
                :per-page="perPage"
                aria-controls="Episodes"
                size="sm"
            ></b-pagination>
        </b-card>
    </div>
</template>

<script>

import Actors from "../Filters/actors";
import Languages from "../Filters/languages";
import Date from "../Filters/date";

export default {
    name: "SeriesSingle",
    components: {Date, Languages, Actors},
    data() {
        return {
            currentPage: 1,
            totalRows: 1,
            perPage: 5,
            isBusy: false,
            filters: {
                actors: [],
                languages: [],
                date: {}
            },
            fields: [{
                key: 'name',
                label: 'name',
                sortable: true
            },

                {
                    key: 'languages.name',
                    label: 'Language',
                    sortable: true
                },
                {
                    key: 'actors',
                    sortable: false
                },
                {
                    key: 'views',
                    label: 'Views',
                    sortable: true
                },
                {
                    key: 'id',
                    label: 'Remove',
                    class: 'text-center'
                },

            ]
        }
    }
    ,
    methods: {
        async getEpisodes(ctx) {
            try {
                console.log(ctx);
                const response = await this.$http.get(`${ctx.apiUrl}`, {
                    params: {
                        page: ctx.currentPage,
                        perPage: ctx.perPage,
                        sortBy: ctx.sortBy,
                        sortDesc: ctx.sortDesc,
                        filter: ctx.filter
                    }
                });
                this.totalRows = response.data.total;
                if (response.data.hasOwnProperty('per_page')) {
                    this.perPage = response.data.per_page;
                }
                console.log(response.data);
                return response.data.data;
            } catch (error) {
                return []
            }

        },
        removeEpisode(id) {
            this.$http.delete(`/api/episode/${id}`).then(() => {
                this.$refs['episode-table'].refresh();
            }).catch(() => {

                alert('Error occurred. Please refresh page');
            });
        }
    }
}
</script>

<style scoped>

</style>
