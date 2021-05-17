<template>
    <div id="create-actor">
        <b-card header="New Actor">
            <b-form-group
                label="Actor name"
                label-cols-sm="4"
                content-cols-sm="8"
                label-cols-lg="3"
                content-cols-lg="9"
                class="mb-3"
                label-for="answer"
                :invalid-feedback="response.name.feedback"
            >
                <b-input
                    id="actor"
                    name="actor"
                    @keyup.enter="createActor"
                    v-model="actor.name"
                    :state="response.name.state"
                />
            </b-form-group>
            <template #footer>
                <b-button :disabled="actor.name === ''" variant="success" class="pull-right" @click="createActor">Create
                    New
                </b-button>
            </template>
        </b-card>
    </div>
</template>

<script>
export default {
    name: "ActorCreate",
    data() {
        return {
            actor: {
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
        createActor() {
            if (this.actor.name !== '') {
                this.$http.post('/api/actor', {
                    ...this.actor
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
    watch: {
        'actor.name': function (newVal) {
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
