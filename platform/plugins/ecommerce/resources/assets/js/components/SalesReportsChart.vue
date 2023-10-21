<template>
    <div>
        <div class='btn-group d-block text-end' v-if='filters.length'>
            <a class='btn btn-sm btn-secondary' href='javascript:' data-bs-toggle='dropdown' aria-expanded='false'>
                <i class='fa fa-filter' aria-hidden='true'></i>
                <span>{{ filtering }}</span>
                <i class='fa fa-angle-down '></i>
            </a>
            <ul class='dropdown-menu float-end'>
                <li v-for='(filter) in filters' :key='filter.key'>
                    <a href='#' v-on:click='clickFilter(filter.key, $event)'>
                        {{ filter.text }}
                    </a>
                </li>
            </ul>
        </div>
        <div class='sales-reports-chart'></div>
        <div class='row' v-if='earningSales.length'>
            <div class='col-12'>
                <ul>
                    <li v-for='earningSale in earningSales' :key='earningSale.text'>
                        <i class='fas fa-circle' :style='{ color: earningSale.color }'></i> {{ earningSale.text }}
                    </li>
                </ul>
            </div>
        </div>
        <div class='loading'></div>
    </div>
</template>

<script>

export default {
    props: {
        url: {
            type: String,
            default: null,
            required: true,
        },
        date_from: {
            type: String,
            default: null,
            required: true,
        },
        date_to: {
            type: String,
            default: null,
            required: true,
        },
        format: {
            type: String,
            default: 'dd/MM/yy',
            required: false,
        },
        filters: {
            type: Array,
            default: () => [],
            required: false,
        },
        filterDefault: {
            type: String,
            default: '',
            required: false,
        },
    },
    data: () => {
        return {
            isLoading: true,
            earningSales: [],
            colors: ['#fcb800', '#80bc00'],
            chart: null,
            filtering: '',
            chartFromDate: null,
            chartToDate: null,
        }
    },
    mounted: function() {
        this.setFiltering()

        this.chartFromDate = this.date_from
        this.chartToDate = this.date_to

        this.renderChart()

        $event.on('sales-report-chart:reload', (data) => {
            this.chartFromDate = data.date_from
            this.chartToDate = data.date_to
            this.renderChart()
        })
    },
    methods: {
        setFiltering: function(f = '') {
            if (!f) {
                f = this.filterDefault
            }
            if (this.filters.length) {
                const filter = this.filters.find((x) => x.key == f)
                if (filter) {
                    this.filtering = filter.text
                } else {
                    this.filtering = f
                }
            }
        },
        renderChart: function() {
            if (this.url) {
                axios.get(this.url + '?date_from=' + this.chartFromDate + '&date_to=' + this.chartToDate)
                    .then(res => {
                        if (res.data.error) {
                            Botble.showError(res.data.message)
                        } else {
                            this.earningSales = res.data.data.earningSales
                            const series =  res.data.data.series
                            const colors = res.data.data.colors
                            const categories = res.data.data.dates

                            if (this.chart === null) {
                                this.chart = new ApexCharts(this.$el.querySelector('.sales-reports-chart'), {
                                    series: series,
                                    chart: { height: 350, type: 'area', toolbar: { show: false } },
                                    dataLabels: { enabled: false },
                                    stroke: { curve: 'smooth' },
                                    colors: colors,
                                    xaxis: {
                                        type: 'datetime',
                                        categories: categories,
                                    },
                                    tooltip: { x: { format: this.format } },
                                    noData: {
                                        text: BotbleVariables.languages.tables.no_data,
                                    },
                                })

                                this.chart.render()
                            } else {
                                this.chart.updateOptions({ series, colors, xaxis: {
                                    type: 'datetime',
                                    categories: categories,
                                }})
                            }
                        }
                    })
            }
        },
        clickFilter: function(filter, event) {
            event.preventDefault()
            this.setFiltering('...')

            const that = this
            axios.get(that.url + '?date_from=' + this.chartFromDate + '&date_to=' + this.chartToDate, {
                params: {
                    filter,
                },
            })
                .then(res => {
                    if (res.data.error) {
                        Botble.showError(res.data.message)
                    } else {
                        that.earningSales = res.data.data.earningSales
                        const options = {
                            xaxis: {
                                type: 'datetime',
                                categories: res.data.data.dates,
                            },
                            series: res.data.data.series,
                        }
                        if (res.data.data.colors) {
                            options.colors = res.data.data.colors
                        }
                        this.chart.updateOptions(options)
                    }
                    this.setFiltering(filter)
                })

        },
    },
}
</script>
