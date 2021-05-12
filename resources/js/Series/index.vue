<template>
    <div id="Series">
        <b-card header="Series">
            <b-table api-url="/api/series"
                     id="series"
                     :busy.sync="isBusy"
                     :items="getSeries"
                     :fields="fields"
                     :current-page="currentPage"
                     :per-page="perPage"
                     table-variant="light"
                     head-variant="light"
                     :striped="true"
                     :bordered="true"
                     :outlined="true"
                     ref="series-table"
            >
                <template #cell(name)="data" class="text-center">
                    <router-link :to="{name: 'Series Single', params: {series: data.item.id}}">
                        {{ data.item.name }}
                    </router-link>
                </template>
                <template #cell(id)="data" class="text-center">
                    <b-button variant="danger" class="btn-sm" @click="removeSeries(data.item.id)"><i
                        class="fa fa-window-close"></i></b-button>
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
    </div>
</template>

<script>

export default {
    name: "SeriesIndex",
    data() {
        return {
            currentPage: 1,
            totalRows: 1,
            perPage: 5,
            isBusy: false,
            fields: [{
                key: 'name',
            }, {
                key: 'episodes_count',
                label: 'Number Of Episodes'
            },
                {
                    key: 'id',
                    label: 'Remove',
                    class: 'text-center'
                }]
        }
    },
    methods: {
        async getSeries(ctx) {
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

        },
        removeSeries(id) {
            this.$http.delete(`/api/series/${id}`).then((results) => {
                this.$refs['series-table'].refresh();
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
