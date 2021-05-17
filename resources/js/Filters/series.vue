<template>
    <div>
        <multiselect
            name="series"
            :options="series.loaded"
            v-model="series.selected"
            label="name"
            track-by="id"
            :multiple="multiple"
            :searchable="true"
            :loading="isLoading"
            :internal-search="false"
            :clear-on-select="false"
            :close-on-select="!multiple"
            :options-limit="300"
            :limit="3"
            :max-height="600"
            :show-no-results="false"
            :hide-selected="true"
            @search-change="searchSeries"
            placeholder="Series"/>
    </div>
</template>

<script>
import Multiselect from 'vue-multiselect';
import filterMixin from "./filterMixin";

export default {
    name: "series",
    components: {
        Multiselect
    },
    mixins:
        [filterMixin]
    ,
    data() {
        return {
            series: {
                selected: [],
                loaded: []
            },
            isLoading: false
        }
    },
    methods: {
        searchSeries(query) {
            this.$http.get('/api/series', {
                params: {
                    search: query
                }
            }).then((response) => {
                this.series.loaded = response.data.data;
            });
        }
    },
    watch: {
        'series.selected': function (series) {
            const seriesType = Object.prototype.toString.call(series);
            if (seriesType === '[object Array]') {
                this.$emit('selected-series', series.map(item => item.id));
            } else if (seriesType === '[object Object]') {
                this.$emit('selected-series', [series].map(item => item.id));
            }

        }
    }
}
</script>

<style scoped>

</style>
