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
                <date @selected-date="setDate($event)" ref="dateSelector"/>
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
                    <b-th colspan="2">Total</b-th>
                    <b-th v-for="date in total" v-bind:key="total.date">{{ format(date) }}</b-th>
                </b-tr>
                <b-tr>
                    <b-th colspan="2">Difference</b-th>
                    <b-th v-for="date in difference" v-bind:key="difference.date">{{ format(date) }}</b-th>
                </b-tr>
            </template>
            <template #cell()="data">
                <template v-if="!['episode','series'].includes(data.field.key)">
                    {{ format(data.item[data.field.key]) }}
                </template>
                <template v-else-if="data.field.key === 'series'">
                    {{ data.item.series }}
                </template>
                <template v-else>
                    {{ data.item.episode }}
                    <b-button variant="success" @click="data.toggleDetails" class="pull-right">+</b-button>
                </template>
            </template>
            <template #row-details="data">
                <b-table :api-url="`/api/stats/episode?name=${encodeURIComponent(data.item.episode)}`"
                         id="stats"
                         :busy.sync="isBusy"
                         :items="getVideoStats"
                         table-variant="light"
                         head-variant="light"
                         :striped="true"
                         :bordered="true"
                         :outlined="true"
                         :filter="filters"
                >
                    <template #cell()="row" :tdAttr='{style:"min-width: 90px;"}'>

                        <template v-if="row.field.key !== 'videos'">
                            <b-input-group>
                                <b-input :value="row.value"
                                         type="number"
                                         @keyup.enter="update($event.target.value, row.item.videos, row.field.key, $event.target.nextElementSibling.firstChild)"/>
                                <b-input-group-append>
                                    <b-button variant="outline-success"
                                              @click.self="update($event.target.parentElement.previousElementSibling.value, row.item.videos, row.field.key,$event.target)">
                                        &#10003;
                                    </b-button>
                                </b-input-group-append>
                            </b-input-group>
                        </template>
                        <template v-else>
                            <p><a :href="row.item.videos" target="_blank">{{ row.item.videos }}</a></p>
                            <b-row>
                                <b-col>
                                    <b-btn-group
                                        :class="{ 'w-100': errors[row.item.videos] === 'true', 'w-50 pull-right':errors[row.item.videos] === 'false'}">
                                        <b-button v-if="errors[row.item.videos] === 'true'" variant="danger" disabled>
                                            Have
                                            Errors!
                                        </b-button>
                                        <b-button @click.self="fetch(row.item.videos)" variant="success"
                                                  class="pull-right btn-sm">
                                            Fetch data
                                        </b-button>
                                    </b-btn-group>
                                </b-col>
                            </b-row>

                        </template>
                    </template>
                </b-table>
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
import series from "./Filters/series";
import actors from "./Filters/actors";
import languages from "./Filters/languages";
import date from "./Filters/date";

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
            perPage: 50,
            isBusy: false,
            keys: [],
            total: [],
            swapped: false,
            difference: {},
            filters: {
                series: [],
                actors: [],
                languages: [],
                date: {}
            },
            errors: {},
            ids: {},
            formatter() {
                return new Intl.NumberFormat('en-US', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 2,
                });
            },
            firstDateSet: true
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

                for (const series in response.data.data.series) {
                    for (const episode in response.data.data.series[series]) {
                        const data = {};
                        data.series = series;
                        data.episode = episode;
                        for (const v of response.data.data.series[series][episode]) {
                            data[v.date] = v.views;
                        }
                        tableData.push(data);
                    }
                }


                this.totalRows = response.data.total;
                this.perPage = response.data.per_page;

                this.total = response.data.data.total;

                const days = Object.keys(this.total);
                const difference = {};

                for (let i = 0; i < days.length; i++) {
                    if (i === 0) {
                        difference[days[i]] = 0;
                    } else if (this.total[days[i]] === 0) {
                        difference[days[i]] = 0;
                    } else if (i > 0) {

                        difference[days[i]] = this.total[days[i]] - this.total[days[i - 1]];
                    }
                }
                this.difference = difference;


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
                this.ids = response.data.data.ids;
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
        format(number) {
            return this.formatter().format(number);
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
        },
        setDate(dates) {
            if (this.firstDateSet) {
                this.filters.date.start_date = '';
                this.filters.date.end_date = '';
                this.firstDateSet = false;

            } else {
                this.filters.date = dates;

            }
        }
    },
    mounted() {
        this.$refs['dateSelector'].setDates('', '');
    }

}
</script>

<style>

table {
    font-size: 10px !important;

}

.table p {
    line-height: 1 !important;
    margin-bottom: 0;
}

.table td {
    padding: 5px !important;
    max-width: 120px !important;
    width: 120px !important;
    min-width: 120px !important;
}

.table th, .table td {
    padding: 0 !important;
}

table input[type="number"] {
    background: transparent;
    border: 0;
    font-size: 12px !important;
}

td button {

    text-align: center;
    padding: 0 3px 0 3px !important;
    margin: 0 !important;
    font-size: 12px;
}

</style>
