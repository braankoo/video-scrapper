<template>
    <div id="create-language">
        <b-card header="New language">
            <b-form-group
                label="language name"
                label-cols-sm="4"
                content-cols-sm="8"
                label-cols-lg="3"
                content-cols-lg="9"
                class="mb-3"
                label-for="answer"
                :invalid-feedback="response.name.feedback"
            >
                <b-input
                    id="language"
                    name="language"
                    @keyup.enter="createLanguage"
                    v-model="language.name"
                    :state="response.name.state"
                />
            </b-form-group>
            <template #footer>
                <b-button :disabled="language.name === ''" variant="success" class="pull-right" @click="createLanguage">Create
                    New
                </b-button>
            </template>
        </b-card>
    </div>
</template>

<script>
export default {
    name: "languageCreate",
    data() {
        return {
            language: {
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
        createLanguage() {
            if (this.language.name !== '') {
                this.$http.post('/api/language', {
                    ...this.language
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
        'language.name': function (newVal) {
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
