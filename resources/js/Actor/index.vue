<template>
    <div id="actors">
        <b-card header="Actors">
            <b-table api-url="/api/actor"
                     id="actors"
                     :busy.sync="isBusy"
                     :items="getActors"
                     :fields="fields"
                     :current-page="currentPage"
                     :per-page="perPage"
                     table-variant="light"
                     head-variant="light"
                     :striped="true"
                     :bordered="true"
                     :outlined="true"
                     ref="actor-table"
            >
                <template #cell(name)="data" class="text-center">
                    <router-link :to="{ name: 'Edit Actor', params: { actor: data.item.id }}">{{ data.item.name }}
                    </router-link>
                </template>
                <template #cell(id)="data" class="text-center">
                    <b-button variant="danger" class="btn-sm" @click="removeActor(data.item.id)"><i
                        class="fa fa-window-close"></i></b-button>
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
    name: "ActorIndex",
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
        async getActors(ctx) {
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
        removeActor(id) {
            this.$http.delete(`/api/actor/${id}`).then((results) => {
                this.$refs['actor-table'].refresh();
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
