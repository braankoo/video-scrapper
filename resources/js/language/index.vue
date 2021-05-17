<template>
    <div id="Languages">
        <b-card header="Languages">
            <b-table api-url="/api/language"
                     id="Languages"
                     :busy.sync="isBusy"
                     :items="getLanguages"
                     :fields="fields"
                     :current-page="currentPage"
                     :per-page="perPage"
                     table-variant="light"
                     head-variant="light"
                     :striped="true"
                     :bordered="true"
                     :outlined="true"
                     ref="language-table"
            >
                <template #cell(id)="data" class="text-center">
                    <b-button variant="danger" class="btn-sm" @click="removeLanguage(data.item.id)"><i
                        class="fa fa-window-close"></i></b-button>
                </template>
            </b-table>
            <b-pagination
                v-model="currentPage"
                :total-rows="totalRows"
                :per-page="perPage"
                aria-controls="Languages"
                size="sm"
            ></b-pagination>
        </b-card>
    </div>
</template>

<script>
export default {
    name: "languageIndex",
    data() {
        return {
            currentPage: 1,
            totalRows: 1,
            perPage: 5,
            isBusy: false,
            fields: [{
                key: 'name'
            },
                {
                    key: 'id',
                    label: 'Remove',
                    class: 'text-center'
                }
            ]
        }
    }
    ,
    methods: {
        async getLanguages(ctx) {
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
        removeLanguage(id) {
            this.$http.delete(`/api/language/${id}`).then((results) => {
                this.$refs['language-table'].refresh();
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
