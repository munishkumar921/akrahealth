<script setup>
import { ref, computed, watch } from 'vue';
import {router } from '@inertiajs/vue3';
import AuthLayout from "@/Layouts/AuthLayout2.vue";
import { defineProps } from "vue";
import { Line } from 'vue-chartjs'
import { Chart as ChartJS, Title, Tooltip, Legend, LineElement, PointElement, CategoryScale, LinearScale } from 'chart.js'
import jsPDF from 'jspdf';


ChartJS.register(Title, Tooltip, Legend, LineElement, PointElement, CategoryScale, LinearScale)

// only vitals + optional type — removed `result`
const props = defineProps({
    vitals: { type: [Array, Object], default: () => [] },
    type: { type: String, default: '' },
});
const goBack = () => window.history.go(-2); // two steps back

const chartRef = ref(null);

// improved options: nicer x/y titles and ticks
const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { position: 'right' },
        tooltip: { mode: 'index', intersect: false }
    },
    scales: {
        x: {
            display: true,
            ticks: { autoSkip: true, maxRotation: 0, minRotation: 0 }
        },
        y: {
            display: true,
            beginAtZero: true,
            title: {
                display: true,
                // no result.unit — use type only
                text: props.type ? `${props.type}` : 'Value'
            }
        }
    }
};

const currentTime = new Date().toLocaleString();

// Always return a safe, sorted array of readings
const readings = computed(() => {
    const v = props.vitals ?? [];
    let list = [];

    if (Array.isArray(v)) list = v.slice();
    else if (v?.data && Array.isArray(v.data)) list = v.data.slice();
    else if (v?.readings && Array.isArray(v.readings)) list = v.readings.slice();
    else if (v?.values && Array.isArray(v.values)) list = v.values.slice();
    else {
        try { list = Object.values(v || {}).flat(); } catch (e) { list = []; }
    }

    // normalize and filter out empty entries
    list = list.filter(Boolean).map(item => item);

    // try to parse and attach timestamp for sorting
    const parsed = list.map(r => {
        const dstr = r?.date || r?.datetime || r?.created_at || r?.time || r?.observed_at || r?.recorded_at;
        const ts = dstr ? (new Date(dstr)).getTime() : (r?.timestamp || null);
        return { raw: r, ts };
    });

    // sort by timestamp ascending (items with no ts go to end)
    parsed.sort((a, b) => {
        if (a.ts === b.ts) return 0;
        if (a.ts === null) return 1;
        if (b.ts === null) return -1;
        return a.ts - b.ts;
    });

    return parsed.map(p => p.raw);
});

// robust numeric extraction
const readingValue = (r) => {
    if (r == null) return null;
    if (typeof r === 'number') return r;
    if (r.value !== undefined && r.value !== null && r.value !== '') return Number(r.value);
    if (r.result !== undefined && r.result !== null && r.result !== '') return Number(r.result);
    if (r.measurement !== undefined && r.measurement !== null && r.measurement !== '') return Number(r.measurement);
    if (r.text !== undefined && !isNaN(Number(r.text))) return Number(r.text);
    // handle "70 lbs" style strings
    if (typeof r === 'string' && !isNaN(Number(r))) return Number(r);
    if (typeof r === 'string') {
        const m = r.match(/-?\d+(\.\d+)?/);
        if (m) return Number(m[0]);
    }
    return null;
};

// month-year short label (e.g. "Jul '20")
const formatMonthYear = (dateLike) => {
    if (!dateLike) return '';
    const d = new Date(dateLike);
    if (isNaN(d.getTime())) return String(dateLike).slice(0, 10);
    return d.toLocaleString(undefined, { month: 'short', year: '2-digit' });
};

const readingLabel = (r) => {
    if (!r) return '';
    const d = r?.date || r?.datetime || r?.created_at || r?.time || r?.observed_at || r?.recorded_at;
    if (d) return formatMonthYear(d);
    // fallback: if numeric-only reading with no date, return empty label
    return '';
};

const borderColor = '#007bff';
const pointBackground = '#fff';

// build chart data, filter out invalid values
const chartData = computed(() => {
    const list = Array.isArray(readings.value) ? readings.value : [];

    const pairs = list
        .map(r => ({ label: readingLabel(r), value: readingValue(r) }))
        .filter(p => p && p.value !== null && p.value !== undefined && !isNaN(Number(p.value)));

    const labels = pairs.map(p => p.label || '');
    const data = pairs.map(p => Number(p.value));

    return {
        labels,
        datasets: [
            {
                // use type as the dataset label (no result)
                label: props.type || 'Vital',
                data,
                fill: false,
                borderColor,
                backgroundColor: borderColor,
                pointBackgroundColor: pointBackground,
                tension: 0.25,
                pointRadius: 4,
                spanGaps: true,
            }
        ]
    };
});

// redraw chart when data changes
watch(chartData, () => {
    const chart = chartRef.value?.chart;
    if (chart) {
        chart.data = chartData.value;
        chart.update();
    }
}, { deep: true });

// menu items adjusted to use type label (no result)
const menuItems = (label) => [
    { label: "Print Chart", icon: "fa fa-print", action: () => downloadChartAs(label, "print") },
    { label: "Download PNG", icon: "fa fa-image", action: () => downloadChartAs(label, "png") },
    { label: "Download JPG", icon: "fa fa-file-image", action: () => downloadChartAs(label, "jpeg") },
    { label: "Export PDF", icon: "fa fa-file-pdf", action: () => downloadChartAs(label, "pdf") },
];

const downloadChartAs = (resultLabel, format) => {
    const chart = chartRef.value?.chart;
    if (!chart) {
        console.error("Chart not found");
        return;
    }

    const image = chart.toBase64Image();

    if (format === 'print') {
        const newWin = window.open('', '_blank');
        if (newWin) {
            newWin.document.write(`
                <html>
                <head><title>${resultLabel || props.type || 'Chart'}</title></head>
                <body style="margin:0;text-align:center"><img src="${image}" /></body>
                </html>
            `);
            newWin.document.close();
            newWin.print();
        }
        return;
    }

    if (format === 'pdf') {
        const pdf = new jsPDF('landscape');
        const pageWidth = pdf.internal.pageSize.getWidth();
        const pageHeight = pdf.internal.pageSize.getHeight();
        pdf.addImage(image, 'PNG', 10, 10, pageWidth - 20, pageHeight - 20);
        pdf.save(`${resultLabel || props.type || 'chart'}_chart.pdf`);
        return;
    }

    const link = document.createElement('a');
    link.download = `${resultLabel || props.type || 'chart'}_chart.${format}`;
    link.href = chart.toBase64Image(`image/${format}`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
};
</script>
 
<template>
    <AuthLayout title="Result Chart" description="Chart of vitals over time" heading="Result Chart">
        <div class="row">
            <div class="col-lg-12">
                <div class="iq-card">
                    <div class="iq-card-header d-flex justify-content-between">
                             <h5>
                                <!-- removed props.result — show type only -->
                                Chart of {{ props.type || 'Vital' }} over time
                                <small class="text-muted"> — as of {{ currentTime }}</small>
                            </h5>
                            <div class="d-flex">
                                 <button class="btn btn-primary btn-sm mr-2" type="button" @click="goBack">
                                    <i class="fa fa-arrow-left mr-1"></i> Back
                               </button>                            <div class="dropdown" style="position: relative; display: inline-block;">
                                <button class="btn btn-primary dropdown-toggle d-flex align-items-center" type="button"
                                    id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fa fa-bars"></i>
                                </button>

                                <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton" style="min-width: 200px;">
                                    <li v-for="(item, index) in menuItems(props.type)" :key="index">
                                        <a href="javascript:void(0)" class="dropdown-item" @click="item.action()">
                                            <i :class="[item.icon, 'fa-fw', 'fa-btn mr-1']"></i> {{ item.label }}
                                        </a>
                                    </li>
                                </ul>
                                </div>
                            </div>
                        </div>
                      <div class="iq-card-body" style="height: 360px;">
                        <Line :data="chartData" :options="chartOptions" ref="chartRef" />
                    </div>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>