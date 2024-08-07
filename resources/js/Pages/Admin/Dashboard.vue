<template>
    <AppLayout>
        <div class="bg-white md:bg-inherit p-4 md:p-8 rounded-[5px] text-[#000] h-full overflow-y-auto">
            <div class="flex justify-between mt-3 md:mt-0">
                <div>
                    <h2 class="md:block hidden text-xl mb-1">{{ $t('Dashboard') }}</h2>
                    <p class="mb-6 flex items-center leading-6">
                        <span class="mt-1 font-semibold md:font-normal text-xl">{{ $t('Welcome back') }}, {{ user?.first_name }} 👋</span>
                    </p>
                </div>
                <div class="relative">
                    <VueDatePicker v-model="selectedDateRange" range @update:model="updateDateRange" class="absolute right-0" />
                    <button @click="updateDuration" class="bg-primary py-2 px-3 rounded-lg text-white text-center ml-2">
                        {{ $t('Select Duration') }}
                    </button>
                </div>
            </div>
            <div class="md:flex space-x-2 md:space-x-2 mt-4 md:mt-0 mb-8 text-xl md:text-sm hidden">
                <Link href="/admin/organizations/create" class="bg-primary py-2 px-3 rounded-lg text-white text-center">{{ $t('Add School') }}</Link>
                <Link href="/admin/users/create" class="bg-primary py-2 px-3 rounded-lg text-white text-center">{{ $t('Add user') }}</Link>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-x-4 gap-y-2 md:space-y-0">
                <div class="bg-slate-100 md:bg-white col-span-1 rounded-lg p-3" v-for="metric in metrics" :key="metric.title">
                    <div class="flex justify-between items-center">
                        <div>
                            <h2 class="text-slate-600">{{ $t(metric.title) }}</h2>
                            <h1 class="text-xl text-gray-600">{{ formatNumber(metric.value) }}</h1>
                        </div>
                        <div class="flex">
                            <span class="bg-secondary/10 p-3 rounded-full self-start">
                                <svg class="text-secondary" xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24">
                                    <g fill="none" stroke="currentColor" stroke-width="1.5">
                                        <path d="M2 12c0-4.714 0-7.071 1.464-8.536C4.93 2 7.286 2 12 2c4.714 0 7.071 0 8.535 1.464C22 4.93 22 7.286 22 12c0 4.714 0 7.071-1.465 8.535C19.072 22 16.714 22 12 22s-7.071 0-8.536-1.465C2 19.072 2 16.714 2 12Z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m7 14l2.293-2.293a1 1 0 0 1 1.414 0l1.586 1.586a1 1 0 0 0 1.414 0L17 10m0 0v2.5m0-2.5h-2.5"/>
                                    </g>
                                </svg>
                            </span>
                        </div>
                    </div>
                    <div class="text-sm space-x-1 md:block hidden">
                        <Link href="/admin/users" class="flex items-center space-x-1 underline">
                            <span>{{ $t('View all') }}</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m8 16l8-8m0 0h-6m6 0v6"></path>
                            </svg>
                        </Link>
                    </div>
                </div>
            </div>
            <div class="md:grid md:grid-cols-2 gap-x-4 mt-8">
                <div>
                    <div id="chart" class="md:block hidden">
                        <apexchart type="area" height="350" :options="chartOptions" :series="series"></apexchart>
                    </div>
                </div>
                <div class="bg-white rounded-lg py-4 md:px-4 h-[fit-content]">
                    <h1>{{ $t('Recent transactions') }}</h1>
                    <p class="text-sm">{{ $t('Below are your most recent transactions') }}</p>
                    <div v-if="props.payments.length === 0" class="md:p-5 border-2 border-dashed mt-4">
                        <p class="text-sm text-center">{{ $t('You do not have any transactions') }}</p>
                    </div>
                    <div v-else class="space-y-1">
                        <div v-for="(item, index) in props.payments.data" :key="index" class="mt-4 border-l-4 bg-gray-50 flex justify-between p-2 text-sm">
                            <div class="capitalize">{{ item.organization.name }}</div>
                            <div class="self-end">
                                <span class="bg-[#ffe5b4] p-1 rounded-md text-xs px-2 capitalize">{{ item.amount }}</span>
                            </div>
                        </div>
                        <Link href="/admin/payment-logs" class="flex items-center space-x-1 underline pt-3 text-sm">
                            <span>{{ $t('View all') }}</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m8 16l8-8m0 0h-6m6 0v6"></path></svg>
                        </Link>
                    </div>
                </div>
            </div>
            <div class="md:grid md:grid-cols-2 gap-x-4 mt-8">
                <div>
                    <h2 class="text-xl mb-4">{{ $t('Users by Class') }}</h2>
                    <apexchart type="pie" height="350" :options="pieChartOptions" :series="usersByClassSeries"></apexchart>
                </div>
                <div>
                    <h2 class="text-xl mb-4">{{ $t('Users by Gender') }}</h2>
                    <apexchart type="bar" height="350" :options="barChartOptions" :series="usersByGenderSeries"></apexchart>
                </div>
            </div>
            <div class="md:grid md:grid-cols-2 gap-x-4 mt-8">
                <div>
                    <h2 class="text-xl mb-4 flex justify-between items-center">
                        {{ $t('Users by State') }}
                        <button @click="toggleStateView" class="bg-primary py-2 px-3 rounded-lg text-white text-center">
                            {{ isShowingTop8 ? $t('Show Least 8') : $t('Show Top 8') }}
                        </button>
                    </h2>
                    <apexchart type="bar" height="350" :options="stateChartOptions" :series="usersByStateSeries"></apexchart>
                </div>
                <div>
                    <h2 class="text-xl mb-4">{{ $t('Users by Age') }}</h2>
                    <apexchart type="bar" height="350" :options="ageChartOptions" :series="usersByAgeSeries"></apexchart>
                </div>
            </div>
            <div class="md:grid md:grid-cols-1 gap-x-4 mt-8">
                <div>
                    <h2 class="text-xl mb-4">{{ $t('User Activity Heatmap') }}</h2>
                    <apexchart type="heatmap" height="350" :options="heatMapChartOptions" :series="heatMapSeries"></apexchart>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
    import AppLayout from "./Layout/App.vue";
    import { computed, defineProps, ref } from "vue";
    import { Link, usePage } from "@inertiajs/vue3";
    import axios from 'axios';
    import ApexCharts from 'vue3-apexcharts';
    import VueDatePicker from '@vuepic/vue-datepicker';
    import '@vuepic/vue-datepicker/dist/main.css';

    const user = computed(() => usePage().props.auth.user);

    const props = defineProps({
        title: { type: String },
        payments: { type: Object },
        totalRevenue: { type: String },
        studentCount: { type: Number },
        userCount: { type: Number },
        openTickets: { type: Number },
        totalMessages: { type: Number },
        period: { type: Object },
        newStudents: { type: Object },
        newUsers: { type: Object },
        revenue: { type: Object },
        totalAPI: { type: Number },
        totalSubjects: { type: Number },
        totalStates: { type: Number },
        totalClasses: { type: Number },
        usersByClass: { type: Array },
        usersByGender: { type: Array },
        usersByState: { type: Array },
        usersByAge: { type: Array },
        activityHeatMap: { type: Object }
    });

    const selectedDateRange = ref([new Date(), new Date()]);
    const metrics = ref([
        { title: 'API Calls', value: props.totalAPI },
        { title: 'Total Subjects', value: props.totalSubjects },
        { title: 'Total States', value: props.totalStates },
        { title: 'Total Classes', value: props.totalClasses },
        { title: 'Active users', value: props.studentCount },
        { title: 'Total revenue', value: props.totalRevenue },
        { title: 'Open tickets', value: props.openTickets },
        { title: 'Total messages', value: props.totalMessages },
    ]);

    const formatNumber = (value) => {
        if (value >= 1000000) {
            return (value / 1000000).toFixed(1) + 'M';
        } else if (value >= 1000) {
            return (value / 1000).toFixed(1) + 'K';
        } else {
            return value.toString();
        }
    };

    const updateDuration = async () => {
        const [startDate, endDate] = selectedDateRange.value;
        const duration = (endDate - startDate) / (1000 * 60 * 60 * 24); // Calculate the duration in days

        try {
            const response = await axios.get(`/admin/dashboard-data?duration=${duration}`);
            updateMetrics(response.data);
        } catch (error) {
            console.error("Failed to update metrics", error);
        }
    };

    const updateDateRange = () => {
        // This function will be called when the date range is updated
    };

    const updateMetrics = (data) => {
        metrics.value = [
            { title: 'API Calls', value: data.totalAPI },
            { title: 'Total Subjects', value: data.totalSubjects },
            { title: 'Total States', value: data.totalStates },
            { title: 'Total Classes', value: data.totalClasses },
            { title: 'Active users', value: data.studentCount },
            { title: 'Total revenue', value: data.totalRevenue },
            { title: 'Open tickets', value: data.openTickets },
            { title: 'Total messages', value: data.totalMessages },
        ];
        // Update other charts/graphs with the new data
        series[0].data = data.newUsers;
        series[1].data = data.revenue;
        // Update other chart options and series
    };

    const chartOptions = {
        chart: {
            height: 350,
            type: 'area',
            toolbar: {
                show: false
            },
            zoom: {
                enabled: false
            }
        },
        colors: ['#034737'],
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.7,
                opacityTo: 0.9,
                stops: [0, 90, 100]
            }
        },
        dataLabels: {
            enabled: false
        },
        stroke: {
            width: 1,
            curve: 'smooth'
        },
        xaxis: {
            type: 'datetime',
            categories: props.period
        },
        tooltip: {
            x: {
                format: 'dd/MM/yy HH:mm'
            },
        },
    };

    const series = [
        {
            name: 'New Users',
            data: props.newUsers
        },
        {
            name: 'Revenue',
            data: props.revenue
        }
    ];

    const pieChartOptions = {
        labels: props.usersByClass.map(item => item.class),
        colors: ['#034737', '#66DA26', '#FF4560', '#FEB019', '#775DD0', '#00E396', '#0090FF', '#FF9800', '#4CAF50', '#546E7A', '#D4526E', '#8D5B4C', '#F86624', '#A5978B']
    };

    const usersByClassSeries = props.usersByClass.map(item => item.count);

    const barChartOptions = {
        chart: {
            height: 350,
            type: 'bar'
        },
        plotOptions: {
            bar: {
                columnWidth: '50%'
            }
        },
        colors: ['#034737'],
        xaxis: {
            categories: props.usersByGender.map(item => item.gender)
        }
    };

    const usersByGenderSeries = [{
        name: 'Users',
        data: props.usersByGender.map(item => item.count)
    }];

    const sortedStateData = props.usersByState.sort((a, b) => b.count - a.count);
    const top8States = sortedStateData.slice(0, 8);
    const least8States = sortedStateData.slice(-8);

    const isShowingTop8 = ref(true);

    const stateChartOptions = {
        chart: {
            height: 350,
            type: 'bar'
        },
        plotOptions: {
            bar: {
                columnWidth: '50%'
            }
        },
        colors: ['#034737'],
        xaxis: {
            categories: isShowingTop8.value ? top8States.map(item => item.state) : least8States.map(item => item.state)
        }
    };

    const usersByStateSeries = [{
        name: isShowingTop8.value ? 'Top 8 States' : 'Least 8 States',
        data: isShowingTop8.value ? top8States.map(item => item.count) : least8States.map(item => item.count)
    }];

    const ageChartOptions = {
        chart: {
            height: 350,
            type: 'bar'
        },
        plotOptions: {
            bar: {
                columnWidth: '50%'
            }
        },
        colors: ['#034737'],
        xaxis: {
            categories: props.usersByAge.map(item => item.age)
        }
    };

    const usersByAgeSeries = [{
        name: 'Users',
        data: props.usersByAge.map(item => item.count)
    }];

    const heatMapChartOptions = {
        chart: {
            height: 350,
            type: 'heatmap'
        },
        plotOptions: {
            heatmap: {
                shadeIntensity: 0.5,
                colorScale: {
                    ranges: [
                        {
                            from: 0,
                            to: 10,
                            color: '#00A100',
                            name: '0-10'
                        },
                        {
                            from: 11,
                            to: 20,
                            color: '#128FD9',
                            name: '11-20'
                        },
                        {
                            from: 21,
                            to: 30,
                            color: '#FFB200',
                            name: '21-30'
                        },
                        {
                            from: 31,
                            to: 40,
                            color: '#FF0000',
                            name: '31-40'
                        }
                    ]
                }
            }
        },
        dataLabels: {
            enabled: false
        },
        xaxis: {
            type: 'category',
            categories: ['12 AM', '1 AM', '2 AM', '3 AM', '4 AM', '5 AM', '6 AM', '7 AM', '8 AM', '9 AM', '10 AM', '11 AM', '12 PM', '1 PM', '2 PM', '3 PM', '4 PM', '5 PM', '6 PM', '7 PM', '8 PM', '9 PM', '10 PM', '11 PM']
        }
    };

    const heatMapSeries = Object.keys(props.activityHeatMap).map(day => ({
        name: day,
        data: props.activityHeatMap[day].map((count, hour) => ({
            x: `${hour} AM/PM`,
            y: count
        }))
    }));

    const toggleStateView = () => {
        isShowingTop8.value = !isShowingTop8.value;
        stateChartOptions.xaxis.categories = isShowingTop8.value ? top8States.map(item => item.state) : least8States.map(item => item.state);
        usersByStateSeries[0].name = isShowingTop8.value ? 'Top 8 States' : 'Least 8 States';
        usersByStateSeries[0].data = isShowingTop8.value ? top8States.map(item => item.count) : least8States.map(item => item.count);
    };
</script>

<style scoped>
/* Add any custom styles here */
</style>
