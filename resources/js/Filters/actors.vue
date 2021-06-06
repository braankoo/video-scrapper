<template>
    <div>
        <multiselect
            :options="actors.loaded"
            v-model="actors.selected"
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
            @search-change="searchActors"
            placeholder="Actors"/>

    </div>
</template>

<script>
import Multiselect from 'vue-multiselect';
import filterMixin from "./filterMixin";

export default {
    name: "actors",
    components: {
        Multiselect
    },
    mixins:
        [filterMixin]
    ,
    data() {
        return {
            actors: {
                selected: [],
                loaded: []
            },
            isLoading: false
        }
    },
    methods: {
        searchActors(query) {
            this.$http.get('/api/actor', {
                params: {
                    search: query
                }
            }).then((response) => {
                this.actors.loaded = response.data.data;
            });
        }
    },
    watch: {
        'actors.selected': function (actors) {
            const actorsType = Object.prototype.toString.call(actors);
            if (actorsType === '[object Array]') {
                this.$emit('selected-actors', actors.map(actor => actor.id));
            } else if (actorsType === '[object Object]') {
                this.$emit('selected-actors', [actors].map(actor => actor.id));
            }
        },
        'preSelected': {
            deep: true,
            handler(val) {
                this.actors.selected = val;
            }
        }
    },
    beforeMount() {
        this.$http.get('/api/actor', {
            params: {
                search: ''
            }
        }).then((response) => {
            this.actors.loaded = response.data.data;
        });
    }
}
</script>

<style scoped>

</style>
