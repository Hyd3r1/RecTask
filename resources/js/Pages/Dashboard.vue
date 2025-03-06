<script setup>
import Layout from '@/Layouts/Layout.vue';
import { Head } from '@inertiajs/vue3';
import { onMounted, ref, watch, watchEffect } from 'vue';

let location = ref({})

let locationDisplayData = ref({});

let weatherChartData = ref({});
let weatherChart = ref({
    chartOptions: {
        chart: {
            id: "weather-chart",
        },
        xaxis: {
            type: "datetime",
            categories: ["2018-09-19T00:00:00.000Z", "2018-09-19T01:30:00.000Z", "2018-09-19T02:30:00.000Z", "2018-09-19T03:30:00.000Z", "2018-09-19T04:30:00.000Z", "2018-09-19T05:30:00.000Z", "2018-09-19T06:30:00.000Z"]
        },
        dataLabels: {
            enabled: false
        },
        tooltip: {
            x: {
                format: 'dd/MM/yy HH:mm'
            }
        }
    },
    series: [
        {
            name: "Temperature",
            data: [10, 30, 1, 999]
        },
        {
            name: "Wind speed",
            data: [10, 30, 1, 1]
        },
        {
            name: "Rain",
            data: [10, 30, 1, 121]
        }
    ]
})

let weatherData = ref({})
let weatherDate = ref([])


const fetchWeatherData = async () => {
    try {
        let weatherResponse = await fetch(route('weather.fetchData', { date: weatherDate?.value?.[0], city: locationDisplayData.value.city, lat: location.value.coords.latitude, long: location.value.coords.longitude }))
        let weatherJson = await weatherResponse.json()
        weatherData.value = weatherJson;
    } catch (e) {
        console.error('Error while fetching weather data: %s', e.message)
    }
}

const fetchWeatherChartData = async () => {
    try {
        let chartResponse = await fetch(route('weather.fetchDataforChart', { date: weatherDate?.value?.[0], city: locationDisplayData.value.city, lat: location.value.coords.latitude, long: location.value.coords.longitude }));
        let chartJson = await chartResponse.json();

        weatherChart.value = {
            chartOptions: {
                chart: {
                    id: "weather-chart",
                },
                xaxis: {
                    type: "datetime",
                    categories: chartJson.categories
                },
                dataLabels: {
                    enabled: false
                },
                tooltip: {
                    x: {
                        format: 'dd/MM/yy HH:mm'
                    }
                }
            },
            series: [
                {
                    name: "Temperature",
                    data: chartJson.temperatures
                },
                {
                    name: "Wind speed",
                    data: chartJson.wind
                },
                {
                    name: "Rain",
                    data: chartJson.rain
                }
            ]
        }
    } catch (e) {
        console.error('Error while fetching chart data: %s', e.message)
    }
}

watch(weatherDate, async () => {
    await fetchWeatherData();
    await fetchWeatherChartData();
})

onMounted(() => {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(async (position) => {
            location.value = position;

            let response = await fetch(route('geolocation.fetchName', { long: location.value.coords.longitude, lat: location.value.coords.latitude }))

            let json = await response.json();

            locationDisplayData.value = json;

            await fetchWeatherData();
            await fetchWeatherChartData();
        }, (error) => {
            console.error(`Error while trying to fetch user location - [${error.code}] ${error.message}`)
        })
    }
});

</script>

<template>

    <Head title="Home" />

    <Layout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Home
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        Your location: {{ locationDisplayData.city }}, {{ locationDisplayData.state }}, {{
                            locationDisplayData.country }} ({{ location.coords?.latitude }}, {{ location.coords?.longitude
                        }})
                    </div>
                </div>
            </div>
        </div>

        <div class="pb-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="pb-2">
                    <vue-tailwind-datepicker as-single v-model="weatherDate"
                        :formatter="{ date: 'YYYY-MM-DD', month: 'MMM' }"
                        :start-from="new Date(2024, 1, 320-11)"
                        :disable-date="(date) => { return date < new Date(2024, 1, 320-11) || date >= new Date() }"
                        placeholder="Select the day for which to show the weather in your location to check the current weather leave blank" />
                </div>
                <div class="overflow-hidden">
                    <div class="flex flex-col">
                        <div class="-m-1.5 overflow-x-auto">
                            <div
                                class="p-1.5 min-w-full inline-block align-middle grid grid-cols-1 gap-4 md:grid-cols-3">
                                <div
                                    class="flex flex-col items-center bg-white border border-gray-200 rounded-xl p-6 md:p-8 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400">
                                    <div class="text-blue-500 text-4xl md:text-5xl">
                                        <span v-if="weatherData?.temperature > 14">☀️</span>
                                        <span v-else>🧊</span>
                                    </div>

                                    <div class="mt-4 text-center">
                                        <p class="text-sm text-gray-500 dark:text-neutral-400">Temperature</p>
                                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{
                                            weatherData?.temperature }}°C</p>
                                    </div>
                                </div>
                                <div
                                    class="flex flex-col items-center bg-white border border-gray-200 rounded-xl p-6 md:p-8 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400">
                                    <div class="text-blue-500 text-4xl md:text-5xl">
                                        💨
                                    </div>

                                    <div class="mt-4 text-center">
                                        <p class="text-sm text-gray-500 dark:text-neutral-400">Wind speed</p>
                                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{
                                            weatherData?.windspeed }} km/h</p>
                                    </div>
                                </div>
                                <div
                                    class="flex flex-col items-center bg-white border border-gray-200 rounded-xl p-6 md:p-8 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400">
                                    <div class="text-blue-500 text-4xl md:text-5xl">
                                        🌧️
                                    </div>

                                    <div class="mt-4 text-center">
                                        <p class="text-sm text-gray-500 dark:text-neutral-400">Rain</p>
                                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{
                                            weatherData?.rain }}
                                            mm</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="pb-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white dark:bg-gray-800">
                    <div class="p-6 flex items-center justify-between mb-3">
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-neutral-200">Weather
                            (24h)</h2>
                        <div class="text-gray-500 dark:text-neutral-400 text-sm">📅 {{ weatherDate[0] ?? 'Today' }}</div>
                    </div>
                </div>
                <div class="overflow-hidden">
                    <div class="flex flex-col">
                        <div class="-m-1.5 overflow-x-auto">
                            <div
                                class="bg-white dark:bg-neutral-900 shadow-lg rounded-xl p-5 border border-gray-200 dark:border-neutral-700">
                                <apexchart width="100%" type="area" height="300" :options="weatherChart.chartOptions"
                                    :series="weatherChart.series"></apexchart>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </Layout>
</template>