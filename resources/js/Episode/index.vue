<template>
    <div id="episodes">
        <b-card header="Episodes">
            <b-table api-url="/api/episode"
                     id="actors"
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
                     ref="episodes-table"
            >
                <template #cell(name)="data">
                    <router-link
                        :to="{name:'Episode Edit', params: {episode: data.item.id}}">
                        {{ data.item.name }}
                    </router-link>
                    <b-button
                        variant="danger"
                        size="small"
                        class="pull-right mr-3"
                        @click="removeEpisode(data.item.id)">x
                    </b-button>
                </template>
            </b-table>
            <b-pagination
                v-model="currentPage"
                :total-rows="totalRows"
                :per-page="perPage"
                aria-controls="actors"
                size="sm"
            ></b-pagination>
        </b-card>
    </div>
</template>

<script>
export default {
    name: "index",
    data() {
        return {
            currentPage: 1,
            totalRows: 1,
            perPage: 50,
            isBusy: false,
            fields: [
                {
                    key: 'series.name',
                    label: 'series'
                },
                {
                    key: 'name'
                },
                {
                    key: 'language.name',
                    label: 'language'
                }
            ]
        }
    }
    ,
    methods: {
        async getEpisodes(ctx) {
            try {

                const response = await this.$http.get(`${ctx.apiUrl}`, {
                    params: {
                        page: ctx.currentPage,
                        perPage: ctx.perPage
                    }
                });
                this.totalRows = response.data.total;
                this.perPage = response.data.per_page;
                return response.data.data;
            } catch (error) {
                return []
            }

        }
        ,
        removeEpisode(id) {
            this.$http.delete(`/api/episode/${id}`).then((results) => {
                this.$refs['episodes-table'].refresh();
            }).catch((errors) => {
                const error = errors.response.data.errors;
                alert('Error occurred. Please refresh page');
            });
        }
    }
}
</script>

<style scoped>

</style>
