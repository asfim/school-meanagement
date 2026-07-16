<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { GraduationCap, Users, DollarSign } from 'lucide-vue-next';

interface RankedStudent {
    id: number;
    student_id: string;
    full_name: string;
    cgpa: number;
    grade: string;
}

interface SemesterData {
    id: number;
    name: string;
    students: RankedStudent[];
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

defineProps<{
    stats: {
        student_count: number;
        teacher_count: number;
        total_tuition_fees: number;
    };
    semesters_best_students: SemesterData[];
}>();
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 max-w-7xl mx-auto space-y-8 animate-in fade-in duration-300">
            <!-- Header -->
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-neutral-900 dark:text-neutral-50">Dashboard Summary</h1>
                <p class="mt-1 text-sm text-neutral-500">Academic insights, statistics, and top student performance charts.</p>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Students Card -->
                <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-xl p-6 shadow-sm flex items-center justify-between hover:shadow-md transition duration-200">
                    <div class="space-y-1">
                        <span class="text-xs font-semibold text-neutral-400 uppercase tracking-wider block">Total Students</span>
                        <span class="text-3xl font-black text-neutral-900 dark:text-neutral-50 block font-mono">{{ stats.student_count }}</span>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-indigo-50 dark:bg-indigo-950 flex items-center justify-center text-neutral-600 dark:text-indigo-400">
                        <GraduationCap class="w-6 h-6" />
                    </div>
                </div>

                <!-- Teachers Card -->
                <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-xl p-6 shadow-sm flex items-center justify-between hover:shadow-md transition duration-200">
                    <div class="space-y-1">
                        <span class="text-xs font-semibold text-neutral-400 uppercase tracking-wider block">Total Faculty Staff</span>
                        <span class="text-3xl font-black text-neutral-900 dark:text-neutral-50 block font-mono">{{ stats.teacher_count }}</span>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-amber-50 dark:bg-amber-950 flex items-center justify-center text-neutral-600 dark:text-amber-400">
                        <Users class="w-6 h-6" />
                    </div>
                </div>

                <!-- Fees Card -->
                <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-xl p-6 shadow-sm flex items-center justify-between hover:shadow-md transition duration-200">
                    <div class="space-y-1">
                        <span class="text-xs font-semibold text-neutral-400 uppercase tracking-wider block">Tuition Fees Collected</span>
                        <span class="text-3xl font-black text-emerald-600 dark:text-emerald-400 block font-mono">৳{{ Number(stats.total_tuition_fees).toFixed(2) }}</span>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-emerald-50 dark:bg-emerald-950 flex items-center justify-center text-neutral-600 dark:text-emerald-400">
                        <DollarSign class="w-6 h-6" />
                    </div>
                </div>
            </div>

            <!-- Leaderboard Section -->
            <div class="space-y-4">
                <!-- Section Header -->
                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-50">Semester Leaderboards</h2>
                    <p class="text-sm text-neutral-500">Top 3 students with the highest CGPA for each semester.</p>
                </div>

                <!-- Semesters Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div
                        v-for="sem in semesters_best_students"
                        :key="sem.id"
                        class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-xl p-5 shadow-sm space-y-4 hover:shadow-md transition duration-200"
                    >
                        <h3 class="text-lg font-bold text-neutral-900 dark:text-neutral-50 border-b border-neutral-100 dark:border-neutral-800 pb-2">
                            {{ sem.name }}
                        </h3>

                        <!-- Empty State -->
                        <div v-if="sem.students.length === 0" class="py-6 text-center text-xs text-neutral-400">
                            No students registered or marks entry completed for this semester.
                        </div>

                        <!-- Top Students List -->
                        <div v-else class="space-y-3">
                            <div
                                v-for="(student, index) in sem.students"
                                :key="student.id"
                                class="flex items-center justify-between p-2.5 rounded-lg bg-neutral-50 dark:bg-neutral-950 border border-neutral-100 dark:border-neutral-850 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition"
                            >
                                <div class="flex items-center gap-3">
                                    <div class="shrink-0 select-none">
                                        <span
                                            v-if="index === 0"
                                            class="w-6 h-6 rounded-full bg-yellow-100 dark:bg-yellow-950 text-yellow-850 dark:text-yellow-400 flex items-center justify-center text-xs font-black border border-yellow-250 dark:border-yellow-900"
                                        >
                                            1
                                        </span>
                                        <span
                                            v-else-if="index === 1"
                                            class="w-6 h-6 rounded-full bg-neutral-200 dark:bg-neutral-850 text-neutral-850 dark:text-neutral-305 flex items-center justify-center text-xs font-black border border-neutral-300 dark:border-neutral-700"
                                        >
                                            2
                                        </span>
                                        <span
                                            v-else-if="index === 2"
                                            class="w-6 h-6 rounded-full bg-amber-100 dark:bg-amber-950 text-amber-850 dark:text-amber-400 flex items-center justify-center text-xs font-black border border-amber-250 dark:border-amber-900"
                                        >
                                            3
                                        </span>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="text-sm font-bold text-neutral-900 dark:text-neutral-50 truncate">
                                            {{ student.full_name }}
                                        </div>
                                        <div class="text-xs text-neutral-400 font-mono">
                                            {{ student.student_id }}
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <div class="text-sm font-black text-neutral-900 dark:text-neutral-50 font-mono">
                                        {{ Number(student.cgpa).toFixed(2) }}
                                    </div>
                                    <div class="text-[10px] font-bold text-neutral-400">
                                        Grade: {{ student.grade }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
