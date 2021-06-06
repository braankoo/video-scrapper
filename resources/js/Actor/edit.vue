<template>
    <div id="update-actor">
        <b-card header="Update Actor Details">
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
                    @keyup.enter="updateActor"
                    v-model="actor.name"
                    :state="response.name.state"
                />
            </b-form-group>
            <b-form-group
                label="Gender"
                label-cols-sm="4"
                content-cols-sm="8"
                label-cols-lg="3"
                content-cols-lg="9"
                class="mb-3"
                label-for="Gender"
                :invalid-feedback="response.gender.feedback"
            >
                <b-form-radio-group
                    id="radio-group-1"
                    v-model="actor.gender"
                    :options="[{text: 'Female',value: 'FEMALE'},{text:'Male',value: 'MALE'}]"
                    name="Gender"
                ></b-form-radio-group>
            </b-form-group>
            <b-form-group
                label="County"
                label-cols-sm="4"
                content-cols-sm="8"
                label-cols-lg="3"
                content-cols-lg="9"
                class="mb-3"
                label-for="Gender"
                :invalid-feedback="response.country.feedback"
            >
                <multiselect
                    :options="countries.loaded"
                    v-model="countries.selected"
                    label="name"
                    track-by="id"
                    :searchable="true"
                    :loading="countries.isLoading"
                    :internal-search="false"
                    :clear-on-select="false"
                    :close-on-select="true"
                    :multiple="false"
                    :options-limit="300"
                    :limit="3"
                    :max-height="600"
                    :show-no-results="false"
                    :hide-selected="true"
                    @search-change="searchCountries"
                    placeholder="Search Countries"/>
            </b-form-group>
            <template #footer>
                <b-button :disabled="actor.name === ''" variant="success" class="pull-right" @click="updateActor">
                    Update
                    Details
                </b-button>
            </template>
        </b-card>
    </div>
</template>

<script>
import multiselect from 'vue-multiselect';

export default {
    name: "ActorUpdate",
    components: {
        multiselect
    },
    data() {
        return {
            actor: {
                name: '',
                gender: 'FEMALE',
                country: ''
            },
            countries: {
                selected: [],
                loaded: [],
                isLoading: false
            },
            response: {
                name: {
                    state: null,
                    feedback: ''
                },
                gender: {
                    state: null,
                    feedback: null
                },
                country: {
                    state: null,
                    feedback: ''
                }
            }
        }
    },
    methods: {
        updateActor() {
            if (this.actor.name !== '') {
                this.$http.patch(`/api/actor/${this.$route.params.actor}`, {
                    ...this.actor
                }).then(() => {
                    this.response.name.state = true;

                    setTimeout(() => {
                        this.response.name.state = null;
                    }, 1300)

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
        },
        searchCountries(query) {
            this.$http.get('/api/countries', {
                params: {
                    search: query
                }
            }).then((response) => {
                console.log(response);
                this.countries.loaded = response.data.data;
            });
        }
    },
    watch: {
        'countries.selected': function (newVal) {
            if (newVal.hasOwnProperty('id')) {
                this.actor.country = newVal.id;
            }
        }
    },
    beforeMount() {
        this.$http.get(`/api/actor/${this.$route.params.actor}`).then((response) => {
                console.log(response.data);
                this.actor.name = response.data.name;
                this.actor.gender = response.data.gender;
                this.countries.selected = response.data.country;

            }
        ).catch((err) => {
            console.log(err);
        });
    }
}
</script>

<style scoped>

</style>
