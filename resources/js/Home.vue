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
                :filter="{date: top.series.date}"
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
                <b-col>
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
                :filter="{date: top.episodes.date}"
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
                <b-col>
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
                :filter="{gender: 'MALE',date: top.actors.male.date}"
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
                <b-col>
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
                :filter="{gender: 'FEMALE',date: top.actors.female.date}"
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
                    date: '',
                    total: 0
                },
                episodes: {
                    date: '',
                },
                actors: {
                    male: {
                        date: '',
                    },
                    female: {
                        date: '',
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
                        date: ctx.filter.date

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
                        date: ctx.filter.date
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
                        date: ctx.filter.date
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
