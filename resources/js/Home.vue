<template>
    <div>
        <b-card header="Total Views">
            <line-chart
                :styles="{position: 'relative',height: '300px'}"
                :dates="chart.daily.dates"
                :views="chart.daily.views"
                v-if="chart.daily.loaded"
            />
        </b-card>
        <hr>
        <b-card header="Monthly New Views">
            <bar-chart
                :styles="{position: 'relative',height: '300px'}"
                :dates="chart.monthly.dates"
                :views="chart.monthly.views"
                v-if="chart.monthly.loaded"
            ></bar-chart>
        </b-card>
        <hr>
        <b-card header="Top 10 Series">
            <b-row class="mb-3">
                <b-col cols="3">
                    <series @selected-series="top.series.series = $event"/>
                </b-col>
                <b-col cols="3">
                    <actors @selected-actors="top.series.actors = $event"/>
                </b-col>
                <b-col cols="2">
                    <languages @selected-languages="top.series.languages = $event"/>
                </b-col>
                <b-col cols="4">
                    <b-form-datepicker id="series-start-date"
                                       placeholder="Start Date"
                                       v-model="top.series.date"
                                       :date-format-options="{ year: 'numeric', month: 'numeric', day: 'numeric' }"></b-form-datepicker>
                </b-col>
            </b-row>
            <hr>
            <b-table
                api-url="/api/series/top10"
                :items="getSeriesData"
                table-variant="light"
                head-variant="light"
                :striped="true"
                :bordered="true"
                :outlined="true"
                :show-empty="true"
                :filter="{date: top.series.date, series:top.series.series, actors:top.series.actors, languages:top.series.languages}"
            >
                <template #thead-top="data">
                    <b-tr>
                        <b-th>Total</b-th>
                        <b-th>{{ formatter().format(top.series.total) }}</b-th>
                    </b-tr>
                </template>
                <template #cell(views)="data">
                    {{ formatter().format(data.item.views) }}
                </template>
            </b-table>
        </b-card>
        <hr>
        <b-card header="Top 10 Episodes">
            <b-row>
                <b-col cols="3">
                    <series @selected-series="top.episodes.series = $event"/>
                </b-col>
                <b-col cols="3">
                    <actors @selected-actors="top.episodes.actors = $event"/>
                </b-col>
                <b-col cols="2">
                    <languages @selected-languages="top.episodes.languages = $event"/>
                </b-col>
                <b-col cols="4">
                    <b-form-datepicker id="episodes-start-date"
                                       placeholder="Start Date"
                                       v-model="top.episodes.date"
                                       :date-format-options="{ year: 'numeric', month: 'numeric', day: 'numeric' }"></b-form-datepicker>
                </b-col>

            </b-row>
            <hr>
            <b-table
                api-url="/api/episode/top10"
                :items="getEpisodesData"
                table-variant="light"
                head-variant="light"
                :striped="true"
                :bordered="true"
                :outlined="true"
                :show-empty="true"
                :filter="{date: top.episodes.date,series:top.episodes.series, actors:top.episodes.actors, languages:top.episodes.languages}"
            >
                <template #thead-top="data">
                    <b-tr>
                        <b-th>Total</b-th>
                        <b-th>{{ formatter().format(top.episodes.total) }}</b-th>
                    </b-tr>
                </template>
                <template #cell(views)="data">
                    {{ formatter().format(data.item.views) }}
                </template>
            </b-table>
        </b-card>
        <hr>
        <b-card header="Top 10 Male Actors">
            <b-row>
                <b-col cols="3">
                    <series @selected-series="top.actors.male.series = $event"/>
                </b-col>
                <b-col cols="3">
                    <actors @selected-actors="top.actors.male.actors = $event"/>
                </b-col>
                <b-col cols="2">
                    <languages @selected-languages="top.actors.male.languages = $event"/>
                </b-col>
                <b-col cols="4">
                    <b-form-datepicker id="male-actor-start-date"
                                       placeholder="Start Date"
                                       v-model="top.actors.male.date"
                                       :date-format-options="{ year: 'numeric', month: 'numeric', day: 'numeric' }"></b-form-datepicker>
                </b-col>
            </b-row>
            <hr>
            <b-table
                api-url="/api/actors/top10"
                :items="getActorsData"
                :filter="{gender: 'MALE',date: top.actors.male.date,series:top.actors.male.series, actors:top.actors.male.actors, languages:top.actors.male.languages}"
                table-variant="light"
                head-variant="light"
                :striped="true"
                :bordered="true"
                :outlined="true"
                :show-empty="true"

            >
                <template #cell(views)="data">
                    {{ formatter().format(data.item.views) }}
                </template>
            </b-table>
        </b-card>
        <hr>
        <b-card header="Top 10 Female Actors">
            <b-row>
                <b-col cols="3">
                    <series @selected-series="top.actors.female.series = $event"/>
                </b-col>
                <b-col cols="3">
                    <actors @selected-actors="top.actors.female.actors = $event"/>
                </b-col>
                <b-col cols="2">
                    <languages @selected-languages="top.actors.female.languages = $event"/>
                </b-col>
                <b-col cols="4">
                    <b-form-datepicker id="female-actor-start-date"
                                       placeholder="Start Date"
                                       v-model="top.actors.female.date"
                                       :date-format-options="{ year: 'numeric', month: 'numeric', day: 'numeric' }"></b-form-datepicker>
                </b-col>
            </b-row>
            <hr>
            <b-table
                api-url="/api/actors/top10"
                :items="getActorsData"
                :filter="{gender: 'FEMALE',date: top.actors.female.date,series:top.actors.female.series, actors:top.actors.female.actors, languages:top.actors.female.languages}"
                table-variant="light"
                head-variant="light"
                :striped="true"
                :bordered="true"
                :outlined="true"
                :show-empty="true"
            >
                <template #cell(views)="data">
                    {{ formatter().format(data.item.views) }}
                </template>
            </b-table>
        </b-card>
        <hr>
    </div>
</template>

<script>
import LineChart from "./charts/LineChart";
import BarChart from "./charts/BarChart";
import series from "./Filters/series";
import actors from "./Filters/actors";
import languages from "./Filters/languages";
import date from "./Filters/date";

export default {
    name: "Home",
    components: {
        BarChart,
        LineChart,
        series,
        actors,
        languages,
        date
    },
    data() {
        return {
            loaded: false,
            chart: {
                daily: {
                    loaded: false,
                    views: [],
                    dates: []
                },
                monthly: {
                    loaded: false,
                    views: [],
                    dates: []
                }
            },
            top: {
                series: {
                    date: '',
                    total: 0,
                    series: [],
                    actors: [],
                    languages: []
                },
                episodes: {
                    date: '',
                    series: [],
                    actors: [],
                    languages: []
                },
                actors: {
                    male: {
                        date: '',
                        series: [],
                        actors: [],
                        languages: []
                    },
                    female: {
                        date: '',
                        series: [],
                        actors: [],
                        languages: []
                    }
                }
            },
            formatter() {
                return new Intl.NumberFormat('en-US', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 2,
                });
            }
        }
    },
    methods: {
        getDailyViews() {
            this.$http.get(`/api/chart/views/daily`,).then((res) => {
                this.chart.daily.views = res.data.map(res => res.views);
                this.chart.daily.dates = res.data.map(res => res.date);
                this.chart.daily.loaded = true;
            }).catch((err) => {
                console.log(err);
            })
        },
        getMonthlyViews() {
            this.$http.get(`/api/chart/views/monthly`,).then((res) => {
                this.chart.monthly.views = res.data.map(res => res.views);
                this.chart.monthly.dates = res.data.map(res => res.date);
                this.chart.monthly.loaded = true;
            }).catch((err) => {
                console.log(err);
            })
        },
        async getSeriesData(ctx) {
            try {
                const response = await this.$http.get(`${ctx.apiUrl}`, {
                    params: {
                        page: ctx.currentPage,
                        perPage: ctx.perPage,
                        date: ctx.filter.date,
                        series: ctx.filter.series,
                        actors: ctx.filter.actors,
                        languages: ctx.filter.languages

                    }
                });
                this.top.series.total = response.data.total;
                return response.data.data;
            } catch (error) {
                return []
            }

        },
        async getEpisodesData(ctx) {
            try {
                const response = await this.$http.get(`${ctx.apiUrl}`, {
                    params: {
                        page: ctx.currentPage,
                        perPage: ctx.perPage,
                        date: ctx.filter.date,
                        series: ctx.filter.series,
                        actors: ctx.filter.actors,
                        languages: ctx.filter.languages
                    }
                });
                this.top.episodes.total = response.data.total;
                return response.data.data;
            } catch (error) {
                return []
            }
        },
        async getActorsData(ctx) {

            try {
                const response = await this.$http.get(`${ctx.apiUrl}`, {
                    params: {
                        gender: ctx.filter.gender,
                        page: ctx.currentPage,
                        perPage: ctx.perPage,
                        date: ctx.filter.date,
                        series: ctx.filter.series,
                        actors: ctx.filter.actors,
                        languages: ctx.filter.languages
                    }
                });
                return response.data;
            } catch (error) {
                return []
            }

        }
    },
    mounted() {
        this.getDailyViews();
        this.getMonthlyViews();
    }
}
</script>

<style scoped>

</style>
