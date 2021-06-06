<template>
    <div id="Episodes">
        <b-card header="Edit Episode">
            <b-form-group
                label="Episode Name"
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
                    v-model="episode.name"
                    :state="response.name.state"
                />
            </b-form-group>
            <b-form-group
                label="Series"
                label-cols-sm="4"
                content-cols-sm="8"
                label-cols-lg="3"
                content-cols-lg="9"
                class="mb-3"
                label-for="series"
                :invalid-feedback="response.series.feedback"
            >
                <series :multiple="false" @selected-series="series = $event[0]" :pre-selected="preselected.series"/>
                <b-form-invalid-feedback :state="response.series.state">
                    {{ response.series.feedback }}
                </b-form-invalid-feedback>

            </b-form-group>
            <b-form-group
                label="Languages"
                label-cols-sm="4"
                content-cols-sm="8"
                label-cols-lg="3"
                content-cols-lg="9"
                class="mb-3"
                label-for="series"
                :invalid-feedback="response.language.feedback"
            >
                <languages :multiple="false"
                           @selected-languages="language = $event[0]"
                           :pre-selected="preselected.language"/>
                <b-form-invalid-feedback :state="response.language.state">
                    {{ response.language.feedback }}
                </b-form-invalid-feedback>
            </b-form-group>
            <b-form-group
                label="Actors"
                label-cols-sm="4"
                content-cols-sm="8"
                label-cols-lg="3"
                content-cols-lg="9"
                class="mb-3"
                label-for="actors"
                :invalid-feedback="response.actors.feedback"
            >
                <actors @selected-actors="actors = $event"
                        :pre-selected="preselected.actors"
                />
                <b-form-invalid-feedback :state="response.actors.state">
                    {{ response.actors.feedback }}
                </b-form-invalid-feedback>
            </b-form-group>
            <b-card header="Videos">
                <template v-for="(video,index) in videos">
                    <b-row class="mb-2">
                        <b-col cols="4">
                            <b-form-group
                                label="Url"
                                label-cols-sm="2"
                                content-cols-sm="10"
                                label-cols-lg="2"
                                content-cols-lg="10"
                                class="mb-3"
                                :label-for="`video-url-${index}`"
                                :invalid-feedback="response.videos[index].url.feedback"
                            >
                                <b-input
                                    :name="`video-url-${index}`"
                                    type="text"
                                    v-model="videos[index].url"
                                    placeholder="Video Url"
                                    :state="response.videos[index].url.state"/>
                            </b-form-group>
                        </b-col>
                        <b-col cols="3">
                            <b-form-group
                                label="Tube"
                                label-cols-sm="3"
                                content-cols-sm="9"
                                label-cols-lg="3"
                                content-cols-lg="9"
                                class="mb-3"
                                :label-for="`video-url-${index}`"
                                :invalid-feedback="response.videos[index].tube_id.feedback"
                            >
                                <b-select v-model="videos[index].tube_id"
                                          :state="response.videos[index].tube_id.state"
                                          :options="[{value:1,text:'xhamster.com'},{value:2,text:'xnxx.com'},{value:3,text:'pornhub.com'},{value:4,text:'xvideos.com'}]"
                                />
                            </b-form-group>
                        </b-col>
                        <b-col cols="4">
                            <b-form-datepicker
                                v-model="videos[index].created_at"
                                label-no-date-selected="Release Date"
                                :date-format-options="{ year: 'numeric', month: 'numeric', day: 'numeric' }"/>
                        </b-col>
                        <b-col cols="1">
                            <b-button variant="danger" @click="removeVideo(index)" :disabled="videos.length === 1">
                                -
                            </b-button>
                        </b-col>
                    </b-row>
                </template>
            </b-card>
            <br>
            <b-button variant="success" class="p2021_06_06_200926_create_countries_tableull-right" @click="addVideo('','', todayDate())">
                Add Video
            </b-button>
            <b-row>
                <b-col>
                    <b-form-valid-feedback :state="valid" class="alert alert-success">
                        Successfuly Updated
                    </b-form-valid-feedback>
                </b-col>
            </b-row>
            <template #footer>
                <b-button variant="success" @click="storeEpisode">Update episode</b-button>
            </template>
        </b-card>
    </div>
</template>
<script>
import Series from "../Filters/series";
import Languages from "../Filters/languages";
import Actors from "../Filters/actors";
import moment from 'moment';


export default {
    name: "edit",
    components: {Actors, Languages, Series},
    data() {
        return {
            valid: null,
            episode: {
                name: ''
            },
            series: '',
            preselected: {
                series: [],
                languages: [],
                actors: []
            },
            seriesSelected: [],
            actors: [],
            language: [],
            videos: [],
            response: {
                name: {
                    state: null,
                    feedback: ''
                },
                language: {
                    state: null,
                    feedback: ''
                },
                series: {
                    state: null,
                    feedback: ''
                },
                actors: {
                    state: null,
                    feedback: ''
                },
                videos: []
            }
        }
    },
    methods: {
        todayDate() {
            return moment().format('YYYY-MM-DD');
        },
        addVideo(url = '', tubeId = '', created_at) {
            this.videos.push(
                {
                    'url': url,
                    'tube_id': tubeId,
                    'created_at': created_at
                }
            );
            this.response.videos.push(
                {
                    created_at: {
                        state: null,
                        feedback: ''
                    },
                    url: {
                        state: null,
                        feedback: ''
                    },
                    tube_id: {
                        state: null,
                        feedback: ''
                    }

                }
            )
        },
        removeVideo(index) {
            this.videos.splice(index, 1);
        },
        storeEpisode() {
            this.$http.patch(`/api/episode/${this.$route.params.episode}`, {
                name: this.episode.name,
                series: this.series,
                actors: this.actors,
                language: this.language,
                videos: this.videos
            }).then((response) => {
                this.clearFeedbacks();
                this.response.name.state = true;
                this.valid = true;
                for (let i = 0; i < this.response.videos.length; i++) {
                    this.response.videos[i].url.state = true;
                }
                setTimeout(() => {
                    this.response.name.state = null;
                    this.response.series.state = null;
                    this.response.actors.state = null;
                    this.response.language.state = null;
                    for (let i = 0; i < this.response.videos.length; i++) {
                        this.response.videos[i].url.state = null;
                        this.response.videos[i].url.feedback = '';

                        this.response.videos[i]['created_at'].feedback = '';
                        this.response.videos[i]['created_at'].state = null;

                        this.response.videos[i]['tube_id'].feedback = '';
                        this.response.videos[i]['tube_id'].state = null;
                    }
                }, 1500);
            }).catch((error) => {
                this.clearFeedbacks();
                const errors = error.response.data.errors;
                if (error.response.status === 422) {
                    for (let group in errors) {

                        if (errors.hasOwnProperty(group)) {
                            if (Object.prototype.toString.call(errors[group]) === '[object Array]') {
                                if (group === 'videos') {
                                    errors[group].forEach((err, index) => {
                                        for (const videoErr in err) {
                                            this.response[group][index][videoErr].feedback = errors[group][index][videoErr].join(' ');
                                            this.response[group][index][videoErr].state = false;
                                        }
                                    });
                                } else {
                                    this.response[group].feedback = errors[group].join(' ');
                                    this.response[group].state = false;
                                }

                            } else if (Object.prototype.toString.call(errors[group]) === '[object Object]') {
                                if (group === 'videos') {
                                    for (const index in errors[group]) {
                                        for (const videoErr in errors[group][index]) {
                                            this.response[group][index][videoErr].feedback = errors[group][index][videoErr].join(' ');
                                            this.response[group][index][videoErr].state = false;
                                        }
                                    }

                                } else {
                                    this.response[group].feedback = errors[group].join(' ');
                                    this.response[group].state = false;
                                }
                            }

                        }
                    }
                }
            });

        },
        clearFeedbacks() {
            this.valid = null;
            this.response.name.state = null;
            this.response.name.feedback = '';

            this.response.series.state = null;
            this.response.series.feedback = '';

            this.response.language.state = null;
            this.response.language.feedback = '';

            this.response.actors.state = null;
            this.response.actors.feedback = '';


            for (let i = 0; i < this.response.videos.length; i++) {
                this.response.videos[i].created_at.state = null;
                this.response.videos[i].created_at.feedback = '';
                this.response.videos[i].url.feedback = '';
                this.response.videos[i].url.state = null;
                this.response.videos[i].tube_id.feedback = '';
                this.response.videos[i].tube_id.state = null;
            }
        }
    },
    beforeMount() {
        this.$http.get(`/api/episode/${this.$route.params.episode}`).then((response) => {
            //
            this.episode.name = response.data.name;
            this.series = response.data.series;
            this.actors = response.data.actors;

            //
            this.preselected.language = response.data.language;
            this.preselected.series = [response.data.series];
            this.preselected.language = [response.data.language];
            this.preselected.actors = response.data.actors;
            response.data.videos.forEach((video) => {
                this.addVideo(video.url, video.tube_id, video.created_at);
            });
        }).catch((err) => {
            console.log(err);
        });
    }
}
</script>
