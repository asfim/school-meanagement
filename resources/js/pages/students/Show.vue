<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';

interface Student {
    id: number;
    student_id: string;
    full_name_en: string;
    full_name_native: string;
    dob: string;
    gender: string;
    parent_name: string;
    parent_mobile: string;
    permanent_address: string;
    current_address: string;
    class: string;
    section: string;
    roll_number: number;
    admission_date: string;
    blood_group: string | null;
    emergency_contact: string;
    status: 'active' | 'transferred' | 'inactive';
    photo_path: string | null;
}

const props = defineProps<{
    student: Student;
}>();

const printMode = ref<'all' | 'id-card' | 'tc'>('all');

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Students', href: '/students' },
    { title: props.student.full_name_en, href: '#' },
];

function printPage() {
    printMode.value = 'all';
    setTimeout(() => {
        window.print();
    }, 50);
}

function printIdCard() {
    printMode.value = 'id-card';
    setTimeout(() => {
        window.print();
    }, 50);
}

function printTc() {
    printMode.value = 'tc';
    setTimeout(() => {
        window.print();
    }, 50);
}

function handleAfterPrint() {
    printMode.value = 'all';
}

onMounted(() => {
    window.addEventListener('afterprint', handleAfterPrint);
});

onUnmounted(() => {
    window.removeEventListener('afterprint', handleAfterPrint);
});

function issueTc() {
    if (confirm('Are you sure you want to issue a Transfer Certificate (TC)?')) {
        router.post(`/students/${props.student.id}/issue-tc`);
    }
}
</script>

<template>
    <Head :title="student.full_name_en + ' - Profile'" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 max-w-5xl mx-auto space-y-6 print:p-0 print:m-0">
            <!-- Header bar: hidden on print -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 print:hidden">
                <div>
                    <h1 class="text-3xl font-extrabold text-neutral-900 dark:text-neutral-50">Student Profile</h1>
                    <p class="text-sm text-neutral-500">Official student details card, ID generator, and TC issuance center.</p>
                </div>
                <div class="flex gap-2">
                    <Link href="/students" class="px-4 py-2 border border-neutral-300 dark:border-neutral-700 bg-white hover:bg-neutral-50 rounded-lg text-sm font-semibold text-neutral-700 dark:bg-neutral-900 dark:text-neutral-200">
                        &larr; Back
                    </Link>
                    <button
                        v-if="student.status === 'active'"
                        @click="issueTc"
                        class="px-4 py-2 bg-amber-500 text-white hover:bg-amber-600 text-sm font-semibold rounded-lg shadow"
                    >
                        Issue Transfer Certificate
                    </button>
                    <button @click="printPage" class="px-4 py-2 bg-neutral-950 dark:bg-neutral-50 dark:text-neutral-950 hover:bg-neutral-800 text-white text-sm font-semibold rounded-lg shadow">
                        Print Profile
                    </button>
                </div>
            </div>

            <!-- Profile Content Area: hidden when printing specifically ID Card or TC -->
            <div 
                class="grid grid-cols-1 lg:grid-cols-3 gap-6"
                :class="{ 'print:hidden': printMode === 'id-card' || printMode === 'tc', 'print:block': printMode === 'all' }"
            >
                <!-- Left Card: Avatar & Quick Info -->
                <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-xl p-6 shadow-sm flex flex-col items-center text-center">
                    <div class="h-32 w-32 rounded-full bg-neutral-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 overflow-hidden flex items-center justify-center shadow-inner">
                        <img v-if="student.photo_path" :src="`/storage/${student.photo_path}`" class="h-full w-full object-cover" />
                        <svg v-else class="h-16 w-16 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <h2 class="mt-4 text-xl font-bold text-neutral-900 dark:text-neutral-50">{{ student.full_name_en }}</h2>
                    <p class="text-sm font-semibold text-neutral-500">{{ student.student_id }}</p>
                    
                    <div class="mt-4 flex items-center gap-1.5">
                        <span v-if="student.status === 'active'" class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-semibold bg-green-50 text-green-700 dark:bg-green-950/30 dark:text-green-400 border border-green-200 dark:border-green-800">Active Student</span>
                        <span v-else-if="student.status === 'transferred'" class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-semibold bg-amber-50 text-amber-700 dark:bg-amber-950/30 dark:text-amber-400 border border-amber-200 dark:border-amber-800">Transferred (TC Issued)</span>
                        <span v-else class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-semibold bg-neutral-50 text-neutral-700 dark:bg-neutral-950/30 dark:text-neutral-400 border border-neutral-200 dark:border-neutral-800">Inactive</span>
                    </div>

                    <div class="w-full border-t border-neutral-100 dark:border-neutral-800 my-4"></div>

                    <div class="w-full text-left space-y-3 text-sm">
                        <div class="flex justify-between"><span class="text-neutral-500">Class:</span> <span class="font-semibold">{{ student.class }}</span></div>
                        <div class="flex justify-between"><span class="text-neutral-500">Section:</span> <span class="font-semibold">Section {{ student.section }}</span></div>
                        <div class="flex justify-between"><span class="text-neutral-500">Roll Number:</span> <span class="font-semibold">#{{ student.roll_number }}</span></div>
                        <div class="flex justify-between"><span class="text-neutral-500">Blood Group:</span> <span class="font-semibold text-red-650">{{ student.blood_group || 'N/A' }}</span></div>
                    </div>
                </div>

                <!-- Right Card: Full Details -->
                <div class="lg:col-span-2 bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-xl p-6 shadow-sm space-y-6">
                    <div>
                        <h2 class="text-lg font-bold border-b border-neutral-100 dark:border-neutral-800 pb-2 text-neutral-900 dark:text-neutral-100">Personal Information</h2>
                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-3 mt-3 text-sm">
                            <div><dt class="text-neutral-500">Full Name (Native)</dt><dd class="font-semibold mt-0.5">{{ student.full_name_native }}</dd></div>
                            <div><dt class="text-neutral-500">Date of Birth</dt><dd class="font-semibold mt-0.5">{{ new Date(student.dob).toLocaleDateString() }}</dd></div>
                            <div><dt class="text-neutral-500">Gender</dt><dd class="font-semibold capitalize mt-0.5">{{ student.gender }}</dd></div>
                            <div><dt class="text-neutral-500">Admission Date</dt><dd class="font-semibold mt-0.5">{{ new Date(student.admission_date).toLocaleDateString() }}</dd></div>
                        </dl>
                    </div>

                    <div>
                        <h2 class="text-lg font-bold border-b border-neutral-100 dark:border-neutral-800 pb-2 text-neutral-900 dark:text-neutral-100">Parent & Contact Details</h2>
                        <dl class="grid grid-cols-1 md:grid-cols-3 gap-x-4 gap-y-3 mt-3 text-sm">
                            <div><dt class="text-neutral-500">Father/Guardian Name</dt><dd class="font-semibold mt-0.5">{{ student.parent_name }}</dd></div>
                            <div><dt class="text-neutral-500">Primary Mobile</dt><dd class="font-semibold mt-0.5">{{ student.parent_mobile }}</dd></div>
                            <div><dt class="text-neutral-500">Emergency Mobile</dt><dd class="font-semibold mt-0.5">{{ student.emergency_contact }}</dd></div>
                        </dl>
                    </div>

                    <div>
                        <h2 class="text-lg font-bold border-b border-neutral-100 dark:border-neutral-800 pb-2 text-neutral-900 dark:text-neutral-100">Addresses</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3 text-sm">
                            <div class="p-3 bg-neutral-50 dark:bg-neutral-950 rounded-lg"><span class="text-xs text-neutral-500 block mb-1">Current Address</span><span class="font-semibold">{{ student.current_address }}</span></div>
                            <div class="p-3 bg-neutral-50 dark:bg-neutral-950 rounded-lg"><span class="text-xs text-neutral-500 block mb-1">Permanent Address</span><span class="font-semibold">{{ student.permanent_address }}</span></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Interactive printable section -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 print:grid-cols-1">
                <!-- 1. Student ID Card Generator -->
                <div 
                    class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-xl p-6 shadow-sm print:shadow-none print:border-none print:flex print:justify-center"
                    :class="{ 'print:hidden': printMode === 'tc', 'print:block': printMode === 'id-card' || printMode === 'all' }"
                >
                    <div class="flex items-center justify-between mb-4 print:hidden">
                        <h3 class="text-lg font-bold">Student ID Card</h3>
                        <button 
                            @click="printIdCard"
                            class="px-3 py-1 bg-neutral-900 hover:bg-neutral-800 text-white text-xs font-bold rounded shadow-sm"
                        >
                            Print ID Card
                        </button>
                    </div>
                    <div class="flex justify-center print:m-0">
                        <!-- Card shell -->
                        <div class="w-80 h-48 border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 rounded-xl p-4 flex flex-col justify-between shadow relative overflow-hidden font-sans border-t-8 border-t-neutral-950 dark:border-t-neutral-50 print:border-t-neutral-950 print:border print:shadow-none">
                            <!-- School Branding -->
                            <div class="flex items-center justify-between border-b border-neutral-200 dark:border-neutral-800 pb-2">
                                <div class="text-xs font-black uppercase text-neutral-900 dark:text-neutral-100 tracking-wider">Antigravity School</div>
                                <div class="text-[8px] text-neutral-500 uppercase">Student Card</div>
                            </div>
                            <!-- Card Body -->
                            <div class="flex gap-4 items-center my-auto">
                                <div class="h-20 w-16 bg-neutral-200 dark:bg-neutral-800 rounded overflow-hidden flex items-center justify-center border border-neutral-300">
                                    <img v-if="student.photo_path" :src="`/storage/${student.photo_path}`" class="h-full w-full object-cover" />
                                    <svg v-else class="h-10 w-10 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                </div>
                                <div class="text-xs space-y-1">
                                    <div class="font-extrabold text-sm text-neutral-900 dark:text-neutral-100">{{ student.full_name_en }}</div>
                                    <div>ID: <span class="font-bold text-neutral-900 dark:text-neutral-100 font-mono">{{ student.student_id }}</span></div>
                                    <div>Class: <span class="font-semibold">{{ student.class }} (Sec {{ student.section }})</span></div>
                                    <div>Roll: <span class="font-semibold">#{{ student.roll_number }}</span></div>
                                    <div>Emergency: <span class="font-mono text-[10px]">{{ student.emergency_contact }}</span></div>
                                </div>
                            </div>
                            <!-- Footer logo/bar -->
                            <div class="text-[8px] text-neutral-400 text-center uppercase tracking-widest border-t border-neutral-200 dark:border-neutral-800 pt-1.5">
                                Issued: {{ new Date(student.admission_date).getFullYear() }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Transfer Certificate Layout (Visible if transferred) -->
                <div 
                    v-if="student.status === 'transferred'" 
                    class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-xl p-6 shadow-sm print:shadow-none print:border-none print:p-0 col-span-1 md:col-span-2 print:col-span-1"
                    :class="{ 'print:hidden': printMode === 'id-card', 'print:block': printMode === 'tc' || printMode === 'all' }"
                >
                    <div class="flex items-center justify-between mb-4 print:hidden">
                        <h3 class="text-lg font-bold">Transfer Certificate (TC)</h3>
                        <button 
                            @click="printTc"
                            class="px-3 py-1 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold rounded shadow-sm"
                        >
                            Print Transfer Certificate
                        </button>
                    </div>
                    <div class="border-2 border-double border-neutral-300 p-8 rounded-lg bg-white dark:bg-neutral-950 font-serif text-neutral-900 dark:text-neutral-100 space-y-6">
                        <div class="text-center">
                            <h2 class="text-2xl font-black uppercase tracking-wider">Antigravity Model School</h2>
                            <p class="text-xs uppercase tracking-widest font-sans text-neutral-500 mt-1">Academic Registry Section</p>
                            <h4 class="text-lg font-bold underline mt-4">TRANSFER CERTIFICATE</h4>
                        </div>
                        <div class="text-sm leading-loose text-justify space-y-4">
                            <p>
                                This is to certify that <span class="font-bold underline">{{ student.full_name_en }}</span>, 
                                son/daughter of <span class="font-bold underline">{{ student.parent_name }}</span>, 
                                was admitted to this institution on <span class="underline">{{ new Date(student.admission_date).toLocaleDateString() }}</span>.
                            </p>
                            <p>
                                He/She was studying in <span class="font-bold underline">{{ student.class }}</span>, Section <span class="font-bold underline">{{ student.section }}</span> 
                                under Roll Number <span class="font-bold underline">{{ student.roll_number }}</span>. 
                                According to the school registry database, his/her date of birth recorded at admission is 
                                <span class="font-bold underline">{{ new Date(student.dob).toLocaleDateString() }}</span>.
                            </p>
                            <p>
                                The student is leaving the school having cleared all outstanding school tuition fee installments. 
                                His/Her character and conduct during the academic residency here have been found to be exemplary. 
                                We wish him/her the very best in all future academic pursuits.
                            </p>
                        </div>
                        <div class="flex justify-between items-end pt-12 text-sm">
                            <div>
                                <p class="border-t border-neutral-400 pt-2 px-4 text-center">Registrar</p>
                            </div>
                            <div class="text-right">
                                <p class="border-t border-neutral-400 pt-2 px-4 text-center">Principal Seal</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
