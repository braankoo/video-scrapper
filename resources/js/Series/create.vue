<template>
    <div id="create-series">
        <b-card header="New Series">
            <b-form-group
                label="Series name"
                label-cols-sm="4"
                content-cols-sm="8"
                label-cols-lg="3"
                content-cols-lg="9"
                class="mb-3"
                label-for="answer"
                :invalid-feedback="response.name.feedback"
            >
                <b-input
                    id="series"
                    name="series"
                    @keyup.enter="createSeries"
                    v-model="series.name"
                    :state="response.name.state"
                />
            </b-form-group>
            <template #footer>
                <b-button :disabled="series.name === ''" variant="success" class="pull-right" @click="createSeries">
                    Create
                    New
                </b-button>
            </template>
        </b-card>
    </div>
</template>

<script>
export default {
    name: "SeriesCreate",
    data() {
        return {
            series: {
                name: ''
            },
            response: {
                name: {
                    state: null,
                    feedback: ''
                }
            }
        }
    },
    methods: {
        createSeries() {
            if (this.series.name !== '') {
                this.$http.post('/api/series', {
                    ...this.series
                }).then(() => {
                    this.response.name.state = true;
                    setTimeout(() => {
                        this.series.name = '';
                        this.response.name.state = null;
                        this.response.name.feedback = '';
                    }, 1300);

                }).catch((error) => {
                    const errors = error.response.data.errors;

                    if (error.response.status === 422) {
                        for (let error in errors) {
                            if (errors.hasOwnProperty(error)) {
                                this.response[error].feedback = errors[error].join(' ');
                                this.response[error].state = false;
                            }
                        }
                        this.submitting = false;
                    }
                })
            }

        }
    },
    watch: {
        'series.name': function (newVal) {
            if (this.response.name.state || !this.response.name.state) {
                this.response.name.state = null;
                this.response.name.feedback = '';
            }
        }
    }
}
</script>

<style scoped>

</style>
