<script setup>
import { ref, computed } from 'vue';
import AuthLayout from "@/Layouts/AuthLayout2.vue";
import { defineProps } from "vue";
import { Line } from 'vue-chartjs'
import { Chart as ChartJS, Title, Tooltip, Legend, LineElement, PointElement, CategoryScale, LinearScale } from 'chart.js'
import jsPDF from 'jspdf';

ChartJS.register(Title, Tooltip, Legend, LineElement, PointElement, CategoryScale, LinearScale)

const props = defineProps({
    result: Object,
    tests: Array,
});

const chartRef = ref(null);

const chartData = computed(() => ({
    labels: props.tests.map(test => test.time),
    datasets: [
        {
            label: props.result.name,
            backgroundColor: '#f87979',
            data: props.tests.map(test => test.result)
        }
    ]
}));

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false
};

const currentTime = new Date().toLocaleString();

const downloadChartAs = (result, format) => {
    const chart = chartRef.value?.chart;
    if (!chart) {
        console.error("Chart not found");
        return;
    }

    const image = chart.toBase64Image();

    switch (format) {
        case 'print':

            // 🖨️ Print chart
            if (format === 'print') {
                const image = chart.toBase64Image();
                const newWin = window.open('', '_blank');
                if (newWin) {
                    newWin.document.write(`
                                        <html>
                                        <head>
                                            <title>${result?.name || 'Chart Print'}</title>
                                            <style>
                                            body { margin: 0; text-align: center; }
                                            img { width: 100%; max-width: 900px; }
                                            </style>
                                        </head>
                                        <body>
                                            <img src="${image}" />
                                            <script>window.print();<\/script>
                                        </body>
                                        </html>
                                    `);
                    newWin.document.close();
                }
                return;
            }
            break;

        case 'pdf':
            const pdf = new jsPDF('landscape'); // use landscape for wide charts
            const pageWidth = pdf.internal.pageSize.getWidth();
            const pageHeight = pdf.internal.pageSize.getHeight();
            pdf.addImage(image, 'PNG', 10, 10, pageWidth - 20, pageHeight - 20);
            pdf.save(`${result.name || 'chart'}_chart.pdf`);
            break;

        default:
            const link = document.createElement('a');
            link.download = `${result.name || 'chart'}_chart.${format}`;
            link.href = chart.toBase64Image(`image/${format}`);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            break;
    }
};


/**
 * Menu Items
 */
const menuItems = (result) => [
    { label: "Print Chart", icon: "fa fa-print", action: () => downloadChartAs(result, "print") },
    { label: "Download PNG", icon: "fa fa-image", action: () => downloadChartAs(result, "png") },
    { label: "Download JPG", icon: "fa fa-file-image", action: () => downloadChartAs(result, "jpeg") },
    { label: "Export PDF", icon: "fa fa-file-pdf", action: () => downloadChartAs(result, "pdf") },
];
</script>
<template>
    <AuthLayout title="Result Chat" description="Chat about this result" heading="Result Chat">
        <div class="row">
            <div class="col-lg-12">
                <div class="iq-card">
                    <div class="iq-card-header d-flex justify-content-between">
                        <div class="iq-header-title d-flex   justify-content-between">
                            <h5>
                                Chart of {{ result?.name }} over time for {{ result?.patient?.name }} as of {{
                                    currentTime }}
                            </h5>
                            <div class="dropdown" style="position: relative; display: inline-block;">
                                <!-- Button -->
                                <button class="btn btn-primary dropdown-toggle d-flex align-items-center " type="button"
                                    id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true"
                                    aria-expanded="false">
                                    <i class="fa fa-bars"></i>
                                </button>

                                <!-- Dropdown Menu -->
                                <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton"
                                    style="min-width: 200px;">
                                    <li v-for="(item, index) in menuItems(result)" :key="index">
                                        <a v-if="item.href" :href="item.href" target="_blank" class="dropdown-item"
                                            style="cursor: pointer;">
                                            <i :class="[item.icon, 'fa-fw', 'fa-btn mr-1']"></i> {{ item.label }}
                                        </a>
                                        <a v-else href="javascript:void(0)" class="dropdown-item"
                                            style="cursor: pointer;" @click="item.action()">
                                            <i :class="[item.icon, 'fa-fw', 'fa-btn mr-1']"></i> {{ item.label }}
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="iq-card-body">
                        <Line :data="chartData" :options="chartOptions" ref="chartRef" />
                    </div>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>
