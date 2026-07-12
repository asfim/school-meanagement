<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

interface TeacherAttendanceRow {
    id: number;
    teacher_id: string;
    full_name: string;
    designation: string;
    attendance_status: 'present' | 'absent' | 'late' | 'leave';
    remarks: string;
}

const props = defineProps<{
    teachers: TeacherAttendanceRow[];
    date: string;
}>();

const selectedDate = ref(props.date);

// When date changes, refresh page via Inertia to load attendance records for that day
watch(selectedDate, (newDate) => {
    router.get('/teachers/attendance', { date: newDate }, { preserveState: true });
});

const form = useForm({
    date: props.date,
    records: props.teachers.map(t => ({
        id: t.id,
        attendance_status: t.attendance_status,
        remarks: t.remarks,
    })),
});

// If props.teachers change, sync back to form
watch(() => props.teachers, (newVal) => {
    form.records = newVal.map(t => ({
        id: t.id,
        attendance_status: t.attendance_status,
        remarks: t.remarks,
    }));
}, { deep: true });

function submit() {
    form.post('/teachers/attendance', {
        onSuccess: () => {
            alert('Attendance records saved successfully.');
        }
    });
}

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Teachers', href: '/teachers' },
    { title: 'Faculty Attendance Log', href: '#' },
];
</script>

<template>
    <Head title="Faculty Attendance Log" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 max-w-5xl mx-auto space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-extrabold text-neutral-900 dark:text-neutral-50">Faculty Attendance Log</h1>
                    <p class="text-sm text-neutral-500">Log daily attendance for teaching staff and monitor check-ins.</p>
                </div>
                <div class="flex items-center gap-2">
                    <label class="text-sm font-semibold text-neutral-600 dark:text-neutral-400">Log Date:</label>
                    <input
                        v-model="selectedDate"
                        type="date"
                        class="rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-950 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:outline-none"
                    />
                </div>
            </div>

            <!-- Attendance Form Shell -->
            <form @submit.prevent="submit" class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-xl p-6 shadow-sm space-y-6">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="bg-neutral-50 dark:bg-neutral-950 text-neutral-500 dark:text-neutral-400 font-semibold border-b border-neutral-200 dark:border-neutral-800">
                                <th class="p-4">Teacher ID</th>
                                <th class="p-4">Faculty Name</th>
                                <th class="p-4">Designation</th>
                                <th class="p-4">Status Option</th>
                                <th class="p-4">Remarks</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200 dark:divide-neutral-800">
                            <tr v-for="(teacher, index) in teachers" :key="teacher.id" class="text-neutral-700 dark:text-neutral-300">
                                <td class="p-4 font-mono font-medium text-neutral-950 dark:text-neutral-100">{{ teacher.teacher_id }}</td>
                                <td class="p-4 font-bold text-neutral-950 dark:text-neutral-100">{{ teacher.full_name }}</td>
                                <td class="p-4 text-xs">{{ teacher.designation }}</td>
                                <td class="p-4">
                                    <select
                                        v-model="form.records[index].attendance_status"
                                        class="rounded-lg border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-2 py-1 text-sm focus:outline-none"
                                        :class="{
                                            'text-green-600 bg-green-50/55 font-semibold': form.records[index].attendance_status === 'present',
                                            'text-red-600 bg-red-50/55 font-semibold': form.records[index].attendance_status === 'absent',
                                            'text-amber-600 bg-amber-50/55 font-semibold': form.records[index].attendance_status === 'late',
                                            'text-blue-600 bg-blue-50/55 font-semibold': form.records[index].attendance_status === 'leave',
                                        }"
                                    >
                                        <option value="present">Present</option>
                                        <option value="absent">Absent</option>
                                        <option value="late">Late</option>
                                        <option value="leave">Leave</option>
                                    </select>
                                </td>
                                <td class="p-4">
                                    <input
                                        v-model="form.records[index].remarks"
                                        type="text"
                                        placeholder="Add note..."
                                        class="w-full rounded border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-2 py-1 text-xs text-neutral-900 dark:text-neutral-100 focus:outline-none"
                                    />
                                </td>
                            </tr>
                            <tr v-if="teachers.length === 0">
                                <td colspan="5" class="p-8 text-center text-neutral-500">No teachers found in registry.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-neutral-100 dark:border-neutral-800 pt-4 flex justify-end gap-3">
                    <Link href="/teachers" class="px-4 py-2 border border-neutral-300 dark:border-neutral-700 text-sm font-semibold rounded-lg text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800">Back</Link>
                    <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-neutral-950 dark:bg-neutral-50 dark:text-neutral-950 hover:bg-neutral-800 text-sm font-semibold rounded-lg text-white shadow">
                        Save Logs
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
