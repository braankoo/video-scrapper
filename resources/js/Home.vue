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
            <b-row>
                <b-col>
                    <b-form-datepicker id="series-start-date"
                                       placeholder="Start Date"
                                       v-model="top.series.range.startDate"
                                       :date-format-options="{ year: 'numeric', month: 'numeric', day: 'numeric' }"></b-form-datepicker>
                </b-col>
                <b-col>
                    <b-form-datepicker id="series-end-date"
                                       placeholder="End Date"
                                       v-model="top.series.range.endDate"
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
                :filter="{startDate: top.series.range.startDate, endDate: top.series.range.endDate}"
            >
                <template #thead-top="data">
                    <b-tr>
                        <b-th>Total</b-th>
                        <b-th>{{ top.series.total }}</b-th>
                    </b-tr>
                </template>
            </b-table>
        </b-card>
        <hr>
        <b-card header="Top 10 Episodes">
            <b-row>
                <b-col>
                    <b-form-datepicker id="episodes-start-date"
                                       placeholder="Start Date"
                                       v-model="top.episodes.range.startDate"
                                       :date-format-options="{ year: 'numeric', month: 'numeric', day: 'numeric' }"></b-form-datepicker>
                </b-col>
                <b-col>
                    <b-form-datepicker id="episodes-end-date"
                                       placeholder="End Date"
                                       v-model="top.episodes.range.endDate"
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
                :filter="{startDate: top.episodes.range.startDate, endDate: top.episodes.range.endDate}"
            >
                <template #thead-top="data">
                    <b-tr>
                        <b-th>Total</b-th>
                        <b-th>{{ top.episodes.total }}</b-th>
                    </b-tr>
                </template>
            </b-table>
        </b-card>
        <hr>
        <b-card header="Top 10 Male Actors">
            <b-row>
                <b-col>
                    <b-form-datepicker id="male-actor-start-date"
                                       placeholder="Start Date"
                                       v-model="top.actors.male.range.startDate"
                                       :date-format-options="{ year: 'numeric', month: 'numeric', day: 'numeric' }"></b-form-datepicker>
                </b-col>
                <b-col>
                    <b-form-datepicker id="male-actor-end-date"
                                       placeholder="End Date"
                                       v-model="top.actors.male.range.endDate"
                                       :date-format-options="{ year: 'numeric', month: 'numeric', day: 'numeric' }"></b-form-datepicker>
                </b-col>

            </b-row>
            <hr>
            <b-table
                api-url="/api/actors/top10"
                :items="getActorsData"
                :filter="{gender: 'MALE',startDate: top.actors.male.range.startDate, endDate: top.actors.male.range.endDate}"
                table-variant="light"
                head-variant="light"
                :striped="true"
                :bordered="true"
                :outlined="true"
                :show-empty="true"

            />
        </b-card>
        <hr>
        <b-card header="Top 10 Female Actors">
            <b-row>
                <b-col>
                    <b-form-datepicker id="female-actor-start-date"
                                       placeholder="Start Date"
                                       v-model="top.actors.female.range.startDate"
                                       :date-format-options="{ year: 'numeric', month: 'numeric', day: 'numeric' }"></b-form-datepicker>
                </b-col>
                <b-col>
                    <b-form-datepicker id="female-actor-end-date"
                                       placeholder="End Date"
                                       v-model="top.actors.female.range.endDate"
                                       :date-format-options="{ year: 'numeric', month: 'numeric', day: 'numeric' }"></b-form-datepicker>
                </b-col>

            </b-row>
            <hr>
            <b-table
                api-url="/api/actors/top10"
                :items="getActorsData"
                :filter="{gender: 'FEMALE',startDate: top.actors.female.range.startDate, endDate: top.actors.female.range.endDate}"
                table-variant="light"
                head-variant="light"
                :striped="true"
                :bordered="true"
                :outlined="true"
                :show-empty="true"
            />
        </b-card>
        <hr>
    </div>
</template>

<script>
import LineChart from "./charts/LineChart";
import BarChart from "./charts/BarChart";

export default {
    name: "Home",
    components: {
        BarChart,
        LineChart
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
                    range: {
                        startDate: '',
                        endDate: '',
                    },
                    total: 0
                },
                episodes: {
                    range: {
                        startDate: '',
                        endDate: '',
                    }
                },
                actors: {
                    male: {
                        range: {
                            startDate: '',
                            endDate: '',
                        }
                    },
                    female: {
                        range: {
                            startDate: '',
                            endDate: '',
                        }
                    }
                }
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
                        startDate: ctx.filter.startDate,
                        endDate: ctx.filter.endDate,
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
                        startDate: ctx.filter.startDate,
                        endDate: ctx.filter.endDate,
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
                        startDate: ctx.filter.startDate,
                        endDate: ctx.filter.endDate
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
