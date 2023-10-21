<template>
    <div>
        <div ref='chartRef' class='revenue-chart'></div>
    </div>
</template>

<script>

const { nextTick } = Vue;

export default {
    props: {
        data: {
            type: Array,
            default: () => [],
            required: true,
        },
    },
    data() {
        return {
            chartData: this.data,
            chartInstance: null
        }
    },
    mounted() {
        this.renderChart()

        $event.on('revenue-chart:reload', (data) => {
            this.chartData = data
            this.renderChart()
        })
    },
    methods: {
        async renderChart() {
            if (!this.chartData.length) {
                return
            }

            let series = []
            let colors = []
            const labels = []
            let total = 0

            this.chartData.map((x) => {
                total += parseFloat(x.value)
                labels.push(x.label)
                colors.push(x.color)
            })
            if (total === 0) {
                this.chartData.map(() => {
                    series.push(0)
                })
            } else {
                this.chartData.map((x) => {
                    series.push(100 / total * parseFloat(x.value))
                })
            }

            if (this.chartInstance === null) {
                this.chartInstance = new ApexCharts(this.$refs.chartRef, {
                    series,
                    colors,
                    chart: { height: '250', type: 'donut' },
                    chartOptions: { labels },
                    plotOptions: { pie: { donut: { size: '71%', polygons: { strokeWidth: 0 } }, expandOnClick: true } },
                    states: { hover: { filter: { type: 'darken', value: .9 } } },
                    dataLabels: { enabled: false },
                    legend: { show: false },
                    tooltip: { enabled: false },
                })

                this.chartInstance.render()
            } else {
                this.chartInstance.updateOptions({ series, colors, chartOptions: { labels } })
            }

            if (jQuery && jQuery().tooltip) {
                $('[data-bs-toggle="tooltip"]').tooltip({ placement: 'top', boundary: 'window' })
            }
        },
    },
}
</script>
