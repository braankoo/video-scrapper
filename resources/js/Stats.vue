<template>
    <b-card header="Series">
        <b-row class="mb-3">
            <b-col cols="3">
                <series @selected-series="filters.series = $event"/>
            </b-col>
            <b-col cols="3">
                <actors @selected-actors="filters.actors = $event"/>
            </b-col>
            <b-col cols="2">
                <languages @selected-languages="filters.languages = $event"/>
            </b-col>
            <b-col cols="4">
                <date @selected-date="filters.date = $event"/>
            </b-col>
        </b-row>
        <b-row class="mb-3">
            <b-col>
                <b-button @click="exportCSV" variant="outline-primary">Export CSV</b-button>
            </b-col>
        </b-row>
        <b-table api-url="/api/stats"
                 id="stats"
                 :busy.sync="isBusy"
                 :items="getStats"
                 :current-page="currentPage"
                 :per-page="perPage"
                 table-variant="light"
                 head-variant="light"
                 :striped="true"
                 :bordered="true"
                 :outlined="true"
                 ref="stats-table"
                 @update:busy="swapTrTh($event)"
                 :filter="filters"
        >
            <template #thead-top="data">
                <b-tr>
                    <b-th>Total</b-th>
                    <b-th v-for="date in total" v-bind:key="total.date">{{ date }}</b-th>
                </b-tr>
            </template>
            <template #cell()="data">
                <template v-if="data.field.key !== 'episode'">
                    {{ data.item[data.field.key] }}
                </template>
                <template v-else>
                    {{ data.item.episode }}
                    <b-button variant="success" @click="data.toggleDetails" class="pull-right">+</b-button>
                </template>
            </template>
            <template #row-details="data">
                <b-table :api-url="`/api/stats/episode?name=${data.item.episode}`"
                         id="stats"
                         :busy.sync="isBusy"
                         :items="getVideoStats"
                         table-variant="light"
                         head-variant="light"
                         :striped="true"
                         :bordered="true"
                         :outlined="true"
                         :filter="filters"
                />
            </template>
        </b-table>
        <b-pagination
            v-model="currentPage"
            :total-rows="totalRows"
            :per-page="perPage"
            aria-controls="series"
            size="sm"
        ></b-pagination>
    </b-card>
</template>

<script>
import series from "./filters/series";
import actors from "./filters/actors";
import languages from "./filters/languages";
import date from "./filters/date";

const fileDownload = require('js-file-download');
export default {
    name: "Stats",
    components: {
        series,
        actors,
        languages,
        date
    },
    data() {
        return {
            currentPage: 1,
            totalRows: 1,
            perPage: 5,
            isBusy: false,
            keys: [],
            total: [],
            swapped: false,
            filters: {
                series: [],
                actors: [],
                languages: [],
                date: {}
            },
            errors: {},
            ids: {}
        }
    },
    methods: {
        async getStats(ctx) {
            try {
                const response = await this.$http.get(`${ctx.apiUrl}`, {
                    params: {
                        page: ctx.currentPage,
                        perPage: ctx.perPage,
                        filter: ctx.filter
                    }
                });
                const tableData = [];
                for (const episode in response.data.data.episodes) {
                    const data = {};
                    data.episode = episode;
                    for (const v of response.data.data.episodes[episode]) {
                        data[v.date] = v.views;
                    }
                    tableData.push(data);
                }
                console.log(tableData);
                this.totalRows = response.data.total;
                this.perPage = response.data.per_page;

                this.total = response.data.data.total;


                return tableData;

            } catch (error) {
                alert('Error occured');
                console.error(error);
                return []
            }

        },
        async getVideoStats(ctx) {
            try {
                const response = await this.$http.get(`${ctx.apiUrl}`, {
                    params: {
                        filter: ctx.filter
                    }
                });
                const tableData = [];
                for (const video in response.data.data.videos) {
                    const data = {};
                    data.videos = video;
                    for (const v of response.data.data.videos[video]) {
                        data[v.date] = v.views;
                    }
                    tableData.push(data);
                }
                return tableData;

            } catch (error) {
                alert('Error occured');
                console.error(error);
                return []
            }

        },
        fetch(video) {
            this.$http.post(`/api/video/fetch/${this.ids[video]}`).then(() => {
                alert('Data retrieving initialized');
            });
        },
        expand() {
            alert('123');
        },
        update(value, video, date, nodeToAddColor) {

            this.$http.post('/api/stats', {
                views: value,
                video: video,
                date: date
            }).then(() => {
                nodeToAddColor.classList.remove('btn-outline-success')
                nodeToAddColor.classList.add('btn-success');
            }).catch(() => {
                alert('123');
            });
        },
        swapTrTh($event) {
            if (!$event && !this.swapped) {
                this.swapped = true;
                this.$nextTick().then(() => {
                    document.querySelector('thead > tr:last-child')
                        .parentNode.insertBefore(
                        document.querySelector('thead > tr:last-child'),
                        document.querySelector('thead > tr:first-child')
                    );
                })

            }

        },
        exportCSV() {
            this.$http.get('/api/stats/csv', {
                responseType: 'blob',
                params: {
                    filters: this.filters
                }
            }).then((response) => {
                fileDownload(response.data, 'stats.csv');
            });
        }
    },
    mounted() {

    }
}
</script>

<style>

table {
    font-size: 12px !important;
}

.table td {
    padding: 5px !important;
    max-width: 120px !important;
}

td button {

    text-align: center;
    padding: 0 3px 0 3px !important;
    margin: 0 !important;
}

td input {
    padding: 5px;
}
</style>
