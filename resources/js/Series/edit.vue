<template>
    <div id="edit-series">
        <b-card header="Edit Series">
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
                    @keyup.enter="updateSeries"
                    v-model="series.name"
                    :state="response.name.state"
                />
            </b-form-group>
            <template #footer>
                <b-button :disabled="series.name === ''" variant="success" class="pull-right" @click="updateSeries">
                    Update Details
                </b-button>
            </template>
        </b-card>
    </div>
</template>

<script>
export default {
    name: "SeriesEdit",
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
        updateSeries() {
            if (this.series.name !== '') {
                this.$http.patch(`/api/series/${this.$route.params.series}`, {
                    ...this.series
                }).then(() => {
                    this.response.name.state = true;

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
    beforeMount() {
        this.$http.get(`/api/series/${this.$route.params.series}`).then((response) => {
            this.series.name = response.data.name;
        }).catch((err) => {
            console.log(err);
        });
    },
    watch: {
        'series.name': function () {
            if (this.response.name.state || !this.response.name.state) {
                this.response.name.state = null;
                this.response.name.feedback = '';
            }
        }
    },

}
</script>

<style scoped>

</style>
