<script>
import {Bar} from 'vue-chartjs'

export default {
    extends: Bar,
    props: {
        views: {
            type: Array,
            default: []
        },
        dates: {
            type: Array,
            default: []
        }
    },
    data() {
        return {
            chartdata: {
                labels: this.dates,
                datasets: [
                    {
                        label: 'Views',
                        borderColor: '#249EBF',
                        pointBackgroundColor: 'white',
                        borderWidth: 1,
                        pointBorderColor: '#249EBF',
                        backgroundColor: 'whitesmoke',
                        data: this.views
                    }
                ]
            },


            options: {
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                            callback: function (value, index, values) {
                                return value.toLocaleString();
                            },
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

    mounted() {
        this.renderChart(this.chartdata, this.options)
    }
}
</script>
