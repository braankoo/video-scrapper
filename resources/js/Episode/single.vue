<template>
    <div id="Videos">
        <b-card header="Videos">
            <b-row class="mb-5">
                <b-col>
                    <date @selected-date="filters.date = $event"/>
                </b-col>
            </b-row>
            <b-table :api-url="`/api/series/${$route.params.series}/episode/${$route.params.episode}`"
                     id="Videos"
                     :busy.sync="isBusy"
                     :items="getVideoss"
                     :fields="fields"
                     :current-page="currentPage"
                     :per-page="perPage"
                     table-variant="light"
                     head-variant="light"
                     :striped="true"
                     :bordered="true"
                     :outlined="true"
                     ref="videos-table"
                     :filter="filters"
            >
                <template #cell(id)="data">
                    <b-button @click.self.prevent="removeVideo(data.item.id)">x</b-button>
                </template>
            </b-table>
            <b-pagination
                v-model="currentPage"
                :total-rows="totalRows"
                :per-page="perPage"
                aria-controls="Videos"
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
    name: "VideosSingle",
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
            fields: [
                {
                    key: 'url',
                    sortable: true
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
        async getVideoss(ctx) {
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
        removeVideo(id) {
            this.$http.delete(`/api/video/${id}`).then(() => {
                this.$refs['videos-table'].refresh();
            }).catch(() => {

                alert('Error occurred. Please refresh page');
            });
        }
    }
}
</script>

<style scoped>

</style>
