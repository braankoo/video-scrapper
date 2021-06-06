<template>
    <div>
        <multiselect
            :options="language.loaded"
            v-model="language.selected"
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
            @search-change="searchLanguages"
            placeholder="Languages"/>
    </div>
</template>

<script>
import Multiselect from 'vue-multiselect';
import filterMixin from "./filterMixin";

export default {
    name: "languages",
    components: {
        Multiselect
    },
    data() {
        return {
            language: {
                selected: [],
                loaded: []
            },
            isLoading: false
        }
    },
    mixins:
        [filterMixin]
    ,
    methods: {

        searchLanguages(query) {
            this.$http.get('/api/language', {
                params: {
                    search: query
                }
            }).then((response) => {
                this.language.loaded = response.data.data;
            });
        }
    },
    watch: {
        'language.selected': function (languages) {
            const languagesType = Object.prototype.toString.call(languages);
            if (languagesType === '[object Array]') {
                this.$emit('selected-languages', languages.map(language => language.id));
            } else if (languagesType === '[object Object]') {
                this.$emit('selected-languages', [languages].map(language => language.id));
            }

        },
        'preSelected': {
            deep: true,
            handler(val) {
                this.language.selected = val;
            }
        }
    },
    beforeMount() {
        this.$http.get('/api/language', {
            params: {
                search: ''
            }
        }).then((response) => {
            this.language.loaded = response.data.data;
        });
    }

}
</script>

<style scoped>

</style>
