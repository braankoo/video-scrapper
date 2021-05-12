<template>
    <div>
        <b-row>
            <b-col>
                <b-form-datepicker
                    v-model="date.start_date"
                    label-no-date-selected="Start Date"
                    :date-format-options="{ year: 'numeric', month: 'numeric', day: 'numeric' }"/>
            </b-col>
            <b-col>
                <b-form-datepicker
                    v-model="date.end_date"
                    label-no-date-selected="End Date"
                    :date-format-options="{ year: 'numeric', month: 'numeric', day: 'numeric' }"/>
            </b-col>
        </b-row>
    </div>
</template>

<script>
import moment from "moment";

export default {
    name: "date",
    data() {
        return {
            date: {
                start_date: moment().subtract(5, 'days').format('YYYY-MM-DD'),
                end_date: moment().format('YYYY-MM-DD')
            }
        }
    },
    watch: {
        date: {
            deep: true,
            immediate: true,
            handler(val) {
                this.$emit('selected-date', val);
            }
        }
    }
}
</script>

<style scoped>

</style>
