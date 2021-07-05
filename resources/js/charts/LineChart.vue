<script>
import {Line, mixins} from 'vue-chartjs'

const {reactiveProp} = mixins


export default {
    extends: Line,
    mixins: [reactiveProp],
    props: {
        options: {
            type: Object,
            default: function () {
                return {
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true
                            },
                            gridLines: {
                                display: true
                            }
                        }],
                        xAxes: [{
                            gridLines: {
                                display: false
                            }
                        }]
                    },
                    legend: {
                        display: true
                    },
                    responsive: true,
                    maintainAspectRatio: false
                }
            }
        },
        updated: {
            type: Number,
            required: true
        }
    },
    mounted() {
        this.renderChart(this.chartData, this.options)
    },
    watch: {
        updated() {
            this.renderChart(this.chartData, this.options);
        }
    },

}
</script>
